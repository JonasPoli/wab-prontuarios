<?php

namespace App\Controller\admin;

use App\Entity\Client;
use App\Entity\Conversation;
use App\Entity\ConversationMessage;
use App\Repository\ConversationRepository;
use App\Service\ClientContextBuilder;
use App\Service\GptService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/client/{clientId}/conversation')]
final class ConversationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ConversationRepository $conversationRepository,
        private readonly GptService $gptService,
        private readonly ClientContextBuilder $contextBuilder,
    ) {}

    /**
     * Lista todas as conversas de um cliente e permite iniciar nova conversa.
     * Rota: GET /admin/client/{clientId}/conversation
     */
    #[Route('', name: 'app_admin_conversation_index', methods: ['GET'])]
    public function index(int $clientId): Response
    {
        $client = $this->entityManager->find(Client::class, $clientId);

        if (!$client) {
            throw $this->createNotFoundException('Cliente não encontrado.');
        }

        $conversations = $this->conversationRepository->findBy(
            ['client' => $client],
            ['createdAt' => 'DESC']
        );

        return $this->render('admin/conversation/index.html.twig', [
            'client'        => $client,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Inicia uma nova conversa com o cliente.
     * Rota: POST /admin/client/{clientId}/conversation/new
     */
    #[Route('/new', name: 'app_admin_conversation_new', methods: ['POST'])]
    public function new(int $clientId): Response
    {
        $client = $this->entityManager->find(Client::class, $clientId);

        if (!$client) {
            throw $this->createNotFoundException('Cliente não encontrado.');
        }

        // Cria uma nova conversa
        $conversation = new Conversation();
        $conversation->setClient($client);
        $conversation->setTitle('Conversa de ' . (new \DateTimeImmutable())->format('d/m/Y H:i'));

        // Pré-processa e salva o contexto do cliente na conversa
        // Isso evita recalcular a cada mensagem — economiza tempo e tokens!
        $context = $this->contextBuilder->build($client);
        $conversation->setContextSnapshot($context);

        $this->entityManager->persist($conversation);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_conversation_show', [
            'clientId' => $clientId,
            'id'       => $conversation->getId(),
        ]);
    }

    /**
     * Exibe a tela de chat de uma conversa específica.
     * Rota: GET /admin/client/{clientId}/conversation/{id}
     */
    #[Route('/{id}', name: 'app_admin_conversation_show', methods: ['GET'])]
    public function show(int $clientId, int $id): Response
    {
        $client = $this->entityManager->find(Client::class, $clientId);
        $conversation = $this->entityManager->find(Conversation::class, $id);

        if (!$client || !$conversation || $conversation->getClient() !== $client) {
            throw $this->createNotFoundException('Conversa não encontrada.');
        }

        return $this->render('admin/conversation/show.html.twig', [
            'client'       => $client,
            'conversation' => $conversation,
            'messages'     => $conversation->getMessages(),
        ]);
    }

    /**
     * Processa uma nova mensagem enviada pelo usuário e chama o GPT.
     * Rota: POST /admin/client/{clientId}/conversation/{id}/message
     *
     * Esta rota é chamada via AJAX (fetch) pelo JavaScript da tela de chat.
     * Retorna JSON com a resposta do GPT.
     */
    #[Route('/{id}/message', name: 'app_admin_conversation_message', methods: ['POST'])]
    public function sendMessage(int $clientId, int $id, Request $request): JsonResponse
    {
        $client = $this->entityManager->find(Client::class, $clientId);
        $conversation = $this->entityManager->find(Conversation::class, $id);

        if (!$client || !$conversation || $conversation->getClient() !== $client) {
            return $this->json(['error' => 'Conversa não encontrada.'], 404);
        }

        // Pega a mensagem enviada pelo usuário via POST (JSON)
        $data = json_decode($request->getContent(), true);
        $userMessage = trim($data['message'] ?? '');

        if (empty($userMessage)) {
            return $this->json(['error' => 'Mensagem vazia.'], 400);
        }

        // Salva a mensagem do usuário no banco
        $userMsg = new ConversationMessage();
        $userMsg->setConversation($conversation);
        $userMsg->setRole('user');
        $userMsg->setContent($userMessage);
        $this->entityManager->persist($userMsg);

        // Monta o histórico de mensagens para enviar ao GPT
        // (mantemos o histórico para que o GPT tenha contexto da conversa)
        $history = [];
        foreach ($conversation->getMessages() as $msg) {
            $history[] = [
                'role'    => $msg->getRole(),
                'content' => $msg->getContent(),
            ];
        }
        // Adiciona a mensagem atual do usuário
        $history[] = ['role' => 'user', 'content' => $userMessage];

        // Monta o prompt de sistema com o contexto do cliente
        $systemPrompt = "Você é um assistente especializado em analisar informações de clientes. "
            . "Responda de forma objetiva e profissional em português brasileiro. "
            . "Use as informações abaixo como base para suas respostas:\n\n"
            . $conversation->getContextSnapshot();

        try {
            // Chama a API do GPT
            $result = $this->gptService->chat($systemPrompt, $history);

            // Salva a resposta do GPT no banco
            $assistantMsg = new ConversationMessage();
            $assistantMsg->setConversation($conversation);
            $assistantMsg->setRole('assistant');
            $assistantMsg->setContent($result['content']);
            $assistantMsg->setTokensUsed($result['tokens_used']);
            $this->entityManager->persist($assistantMsg);

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => $result['content'],
                'tokens'  => $result['tokens_used'],
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erro ao comunicar com o GPT: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deleta uma conversa.
     * Rota: POST /admin/client/{clientId}/conversation/{id}/delete
     */
    #[Route('/{id}/delete', name: 'app_admin_conversation_delete', methods: ['POST'])]
    public function delete(int $clientId, int $id, Request $request): Response
    {
        $client = $this->entityManager->find(Client::class, $clientId);
        $conversation = $this->entityManager->find(Conversation::class, $id);

        if ($conversation && $this->isCsrfTokenValid('delete' . $id, $request->request->getString('_token'))) {
            $this->entityManager->remove($conversation);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_conversation_index', ['clientId' => $clientId]);
    }
}