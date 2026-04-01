# 🤖 Guia de Implementação: Sistema de Chat com IA (GPT) por Cliente

> **Para quem é este guia?** Para um programador que está iniciando na área. Vamos seguir passo a passo, explicando cada ação e cada conceito com cuidado.

---

## 📋 Visão Geral do que vamos construir

Vamos adicionar um botão **"Chat"** na listagem de clientes. Ao clicar nele, vai abrir uma tela de chat (parecida com o ChatGPT) onde você pode fazer perguntas sobre aquele cliente específico. O sistema usa a API do ChatGPT para responder com base nos dados que o cliente tem no sistema.

### Como o sistema vai funcionar:

```
Você → Pergunta sobre o cliente
         ↓
   Sistema monta um "contexto" com os dados do cliente
   (nome, projetos, históricos, transcrições, etc.)
         ↓
   Manda tudo para a API do GPT com a sua pergunta
         ↓
   GPT responde → Sistema salva no banco → Exibe para você
```

### Por que pré-processar o contexto do cliente?

Cada chamada para a API do GPT tem um custo em **tokens** (pense como "créditos"). Quanto mais texto você manda, mais tokens consome. Por isso, vamos criar um campo `context_snapshot` na entidade de conversa que armazena um **resumo pré-processado e comprimido** dos dados do cliente ao iniciar a conversa. Isso evita reprocessar tudo a cada mensagem.

---

## 🗂️ Estrutura de Arquivos que Vamos Criar

```
src/
  Entity/
    Conversation.php          ← NOVA entidade "conversa"
    ConversationMessage.php   ← NOVA entidade "mensagem"
  Repository/
    ConversationRepository.php
    ConversationMessageRepository.php
  Controller/
    admin/
      ConversationController.php  ← NOVO controller
  Service/
    GptService.php            ← Serviço que fala com a API do GPT
    ClientContextBuilder.php  ← Serviço que monta o contexto do cliente
migrations/
  VersionXXX.php              ← Migration do banco gerada automaticamente
templates/
  admin/
    conversation/
      index.html.twig         ← Lista de conversas de um cliente
      show.html.twig          ← Tela do chat (visual estilo ChatGPT)
```

---

## 🔑 PASSO 1 — Configurar as Chaves da API no `.env` e `.env.local`

### O que é o `.env`?

O arquivo `.env` é um arquivo de **configuração** do projeto Symfony. Ele define variáveis de ambiente (configurações) que o sistema usa. Este arquivo **vai para o Git** (repositório), então nunca coloque senhas reais nele.

### O que é o `.env.local`?

O arquivo `.env.local` é o arquivo onde você coloca as **configurações reais** da sua máquina local. Ele está listado no `.gitignore` — ou seja, **nunca vai para o Git**. É aqui que você coloca chaves de API reais.

### O que fazer:

**Abra o arquivo `.env`** e adicione a linha abaixo (com valor fictício, só para documentar):

```dotenv
###> openai ###
# Chave da API do OpenAI (GPT). Obtenha em: https://platform.openai.com/api-keys
# NUNCA coloque a chave real aqui! Use o .env.local para isso.
OPENAI_API_KEY=sua-chave-aqui
OPENAI_MODEL=gpt-4o-mini
###< openai ###
```

**Abra o arquivo `.env.local`** e adicione com a chave real:

```dotenv
OPENAI_API_KEY=sk-proj-xxxxxxxxxxxxxxxxxxxxxxx
OPENAI_MODEL=gpt-4o-mini
```

> **Como obter a chave?** Acesse https://platform.openai.com/api-keys, faça login com sua conta OpenAI e crie uma nova chave.

> **Por que `gpt-4o-mini`?** É o modelo mais barato e rápido da OpenAI que ainda entende bem contexto longo. Ideal para economizar tokens.

---

## 🏗️ PASSO 2 — Criar as Novas Entidades no Banco de Dados

> **O que é uma entidade?** Em Symfony com Doctrine, uma entidade é uma classe PHP que representa uma tabela no banco de dados. Cada propriedade da classe vira uma coluna na tabela.

### 2.1 — Entidade `Conversation` (Conversa)

Crie o arquivo `src/Entity/Conversation.php`:

```php
<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Qual cliente esta conversa pertence
    #[ORM\ManyToOne(inversedBy: 'conversations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Client $client = null;

    // Título da conversa (gerado automaticamente ou pelo usuário)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    // Snapshot do contexto do cliente (pré-processado para economizar tokens)
    // Armazena um resumo comprimido dos dados do cliente no momento de criação da conversa
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contextSnapshot = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(
        targetEntity: ConversationMessage::class,
        mappedBy: 'conversation',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $client): static { $this->client = $client; return $this; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $title): static { $this->title = $title; return $this; }

    public function getContextSnapshot(): ?string { return $this->contextSnapshot; }
    public function setContextSnapshot(?string $contextSnapshot): static { $this->contextSnapshot = $contextSnapshot; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }

    /** @return Collection<int, ConversationMessage> */
    public function getMessages(): Collection { return $this->messages; }

    public function addMessage(ConversationMessage $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConversation($this);
        }
        return $this;
    }

    public function removeMessage(ConversationMessage $message): static
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
        }
        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
```

### 2.2 — Entidade `ConversationMessage` (Mensagem)

Crie o arquivo `src/Entity/ConversationMessage.php`:

```php
<?php

namespace App\Entity;

use App\Repository\ConversationMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationMessageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ConversationMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Qual conversa esta mensagem pertence
    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Conversation $conversation = null;

    // Quem enviou: 'user' (você) ou 'assistant' (GPT)
    #[ORM\Column(length: 20)]
    private ?string $role = null; // 'user' ou 'assistant'

    // O conteúdo da mensagem
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    // Quantos tokens esta mensagem consumiu (para controle de custos)
    #[ORM\Column(nullable: true)]
    private ?int $tokensUsed = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int { return $this->id; }

    public function getConversation(): ?Conversation { return $this->conversation; }
    public function setConversation(?Conversation $conversation): static { $this->conversation = $conversation; return $this; }

    public function getRole(): ?string { return $this->role; }
    public function setRole(string $role): static { $this->role = $role; return $this; }

    public function getContent(): ?string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }

    public function getTokensUsed(): ?int { return $this->tokensUsed; }
    public function setTokensUsed(?int $tokensUsed): static { $this->tokensUsed = $tokensUsed; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
```

### 2.3 — Atualizar a Entidade `Client` para ter a relação com `Conversation`

Abra `src/Entity/Client.php` e adicione:

1. No bloco de `use` no topo, adicione:
```php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
```
(Já existem, só verifique)

2. Adicione a propriedade de relação:
```php
#[ORM\OneToMany(
    targetEntity: Conversation::class,
    mappedBy: 'client',
    cascade: ['persist', 'remove'],
    orphanRemoval: true
)]
private Collection $conversations;
```

3. No `__construct()`, adicione:
```php
$this->conversations = new ArrayCollection();
```

4. Adicione os métodos getter/adder/remover ao final da classe (antes do `}`):
```php
/** @return Collection<int, Conversation> */
public function getConversations(): Collection
{
    return $this->conversations;
}

public function addConversation(Conversation $conversation): static
{
    if (!$this->conversations->contains($conversation)) {
        $this->conversations->add($conversation);
        $conversation->setClient($this);
    }
    return $this;
}

public function removeConversation(Conversation $conversation): static
{
    if ($this->conversations->removeElement($conversation)) {
        if ($conversation->getClient() === $this) {
            $conversation->setClient(null);
        }
    }
    return $this;
}
```

---

## 🗄️ PASSO 3 — Criar os Repositories

> **O que é um Repository?** É uma classe que facilita as buscas no banco de dados para aquela entidade. O Symfony gera um modelo básico.

### 3.1 — `ConversationRepository.php`

Crie `src/Repository/ConversationRepository.php`:

```php
<?php

namespace App\Repository;

use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }
}
```

### 3.2 — `ConversationMessageRepository.php`

Crie `src/Repository/ConversationMessageRepository.php`:

```php
<?php

namespace App\Repository;

use App\Entity\ConversationMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConversationMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConversationMessage::class);
    }
}
```

---

## 🔧 PASSO 4 — Criar a Migration (atualizar o banco de dados)

> **O que é uma Migration?** É uma classe PHP que diz ao banco de dados "crie estas tabelas / adicione estas colunas". É como um histórico de mudanças no banco.

No terminal, dentro da pasta do projeto, rode:

```bash
php bin/console doctrine:migrations:diff
```

Isso vai gerar um arquivo em `migrations/` automaticamente. Depois rode:

```bash
php bin/console doctrine:migrations:migrate
```

Confirme com `yes` quando perguntado. Isso vai criar as tabelas `conversation` e `conversation_message` no banco.

---

## ⚙️ PASSO 5 — Criar os Serviços

> **O que é um Service?** É uma classe PHP que faz uma tarefa específica. Em vez de colocar toda a lógica no Controller, separamos em Services. Isso deixa o código mais organizado e reutilizável.

### 5.1 — `ClientContextBuilder.php` — Monta o contexto do cliente

Este serviço coleta todos os dados relevantes do cliente e cria um texto resumido para ser enviado ao GPT como contexto.

Crie `src/Service/ClientContextBuilder.php`:

```php
<?php

namespace App\Service;

use App\Entity\Client;

class ClientContextBuilder
{
    /**
     * Monta um texto de contexto sobre o cliente para ser enviado ao GPT.
     *
     * Esta função coleta dados do cliente, projetos e históricos e os
     * organiza em um texto estruturado. Quanto mais rico o contexto,
     * melhores serão as respostas — mas mais tokens você vai gastar.
     *
     * A estratégia aqui é enviar apenas os campos mais relevantes e
     * limitar o tamanho das transcrições para economizar tokens.
     */
    public function build(Client $client): string
    {
        $lines = [];

        // Informações básicas do cliente
        $lines[] = "=== DADOS DO CLIENTE ===";
        $lines[] = "Nome: " . ($client->getName() ?? 'N/A');
        $lines[] = "Nome Fantasia: " . ($client->getFantasyName() ?? 'N/A');
        $lines[] = "Documento: " . ($client->getDocument() ?? 'N/A');
        $lines[] = "E-mail: " . ($client->getMail() ?? 'N/A');
        $lines[] = "Telefone: " . ($client->getPhone1() ?? 'N/A');
        $lines[] = "Tipo: " . ($client->getType() ?? 'N/A');
        $lines[] = "Status: " . ($client->getStatus() ?? 'N/A');

        if ($client->getObs()) {
            $lines[] = "Observações: " . $client->getObs();
        }

        // Projetos do cliente
        $projects = $client->getClientProjects();
        if ($projects->count() > 0) {
            $lines[] = "";
            $lines[] = "=== PROJETOS (" . $projects->count() . " no total) ===";

            foreach ($projects as $project) {
                $lines[] = "";
                $lines[] = "--- Projeto: " . $project->getTitle() . " ---";

                if ($project->getDescription()) {
                    $lines[] = "Descrição: " . $project->getDescription();
                }
                if ($project->getFullDescription()) {
                    // Limita para não gastar muitos tokens
                    $lines[] = "Descrição Completa: " . mb_substr($project->getFullDescription(), 0, 500);
                }
                if ($project->getDateStart()) {
                    $lines[] = "Início: " . $project->getDateStart()->format('d/m/Y');
                }
                if ($project->getDateEnd()) {
                    $lines[] = "Término: " . $project->getDateEnd()->format('d/m/Y');
                }
                if ($project->getObs()) {
                    $lines[] = "Obs: " . mb_substr($project->getObs(), 0, 300);
                }

                // Históricos do projeto
                $histories = $project->getClientProjectHistories();
                if ($histories->count() > 0) {
                    $lines[] = "Históricos (" . $histories->count() . " registros):";

                    // Limita a 5 históricos mais recentes para economizar tokens
                    $historyCount = 0;
                    foreach ($histories as $history) {
                        if ($historyCount >= 5) {
                            $lines[] = "  ... (e mais " . ($histories->count() - 5) . " históricos omitidos)";
                            break;
                        }

                        if ($history->getOccurredAt()) {
                            $lines[] = "  [" . $history->getOccurredAt()->format('d/m/Y') . "]";
                        }
                        if ($history->getSummary()) {
                            $lines[] = "  Resumo: " . mb_substr($history->getSummary(), 0, 300);
                        }
                        if ($history->getTranscript()) {
                            // Transcrições podem ser longas — limitamos bastante
                            $lines[] = "  Transcrição (trecho): " . mb_substr($history->getTranscript(), 0, 500);
                        }

                        $historyCount++;
                    }
                }
            }
        } else {
            $lines[] = "";
            $lines[] = "Este cliente não possui projetos cadastrados.";
        }

        return implode("\n", $lines);
    }
}
```

### 5.2 — `GptService.php` — Se comunica com a API do GPT

Crie `src/Service/GptService.php`:

```php
<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GptService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
        private readonly string $model = 'gpt-4o-mini'
    ) {}

    /**
     * Envia uma pergunta para o GPT e retorna a resposta.
     *
     * @param string $systemPrompt  Instruções iniciais para o GPT (o contexto do cliente)
     * @param array  $messages      Histórico de mensagens no formato [{role, content}, ...]
     * @return array ['content' => string, 'tokens_used' => int]
     */
    public function chat(string $systemPrompt, array $messages): array
    {
        // Monta o array de mensagens no formato que a API do OpenAI espera
        $apiMessages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Adiciona o histórico de mensagens anteriores
        foreach ($messages as $msg) {
            $apiMessages[] = [
                'role'    => $msg['role'],    // 'user' ou 'assistant'
                'content' => $msg['content'],
            ];
        }

        // Faz a chamada HTTP para a API da OpenAI
        $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model'       => $this->model,
                'messages'    => $apiMessages,
                'max_tokens'  => 1000,      // Limita o tamanho da resposta
                'temperature' => 0.7,       // Controla a criatividade (0=conservador, 1=criativo)
            ],
        ]);

        $data = $response->toArray();

        return [
            'content'     => $data['choices'][0]['message']['content'] ?? 'Não foi possível obter resposta.',
            'tokens_used' => $data['usage']['total_tokens'] ?? 0,
        ];
    }
}
```

### 5.3 — Registrar o `GptService` como serviço com injeção de parâmetros

Abra o arquivo `config/services.yaml` e adicione:

```yaml
services:
    # ... (mantenha o que já existe)

    App\Service\GptService:
        arguments:
            $apiKey: '%env(OPENAI_API_KEY)%'
            $model: '%env(OPENAI_MODEL)%'
```

> **O que isso faz?** Diz ao Symfony: "quando precisar criar o `GptService`, passe os valores das variáveis de ambiente como argumentos do construtor."

---

## 🎮 PASSO 6 — Criar o Controller

Crie `src/Controller/admin/ConversationController.php`:

```php
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
```

---

## 🎨 PASSO 7 — Criar os Templates Twig

### 7.1 — `index.html.twig` — Lista de Conversas do Cliente

Crie `templates/admin/conversation/index.html.twig`:

```twig
{% extends 'admin/admin-template.html.twig' %}

{% block title %}Conversas — {{ client.name }}{% endblock %}

{% block content %}

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="mb-0">Chat com IA</h1>
        <div class="text-body-secondary small">Cliente: <strong>{{ client.name }}</strong></div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ path('app_admin_client_show', {'id': client.id}) }}" class="btn btn-outline-secondary btn-sm">
            ← Voltar ao Cliente
        </a>
        <form method="POST" action="{{ path('app_admin_conversation_new', {'clientId': client.id}) }}">
            <button type="submit" class="btn btn-primary btn-sm">
                + Nova Conversa
            </button>
        </form>
    </div>
</div>

{% if conversations is empty %}
    <div class="text-center py-5 text-body-secondary">
        <div style="font-size:3rem;">💬</div>
        <p class="mt-2">Nenhuma conversa ainda.<br>Clique em <strong>Nova Conversa</strong> para começar.</p>
    </div>
{% else %}
    <div class="list-group">
        {% for conversation in conversations %}
            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <a href="{{ path('app_admin_conversation_show', {'clientId': client.id, 'id': conversation.id}) }}"
                   class="text-decoration-none text-body flex-grow-1">
                    <div class="fw-semibold">{{ conversation.title }}</div>
                    <div class="small text-body-secondary">
                        {{ conversation.messages|length }} mensagem(ns) ·
                        {{ conversation.createdAt ? conversation.createdAt|date('d/m/Y H:i') : '' }}
                    </div>
                </a>
                <form method="POST"
                      action="{{ path('app_admin_conversation_delete', {'clientId': client.id, 'id': conversation.id}) }}"
                      onsubmit="return confirm('Excluir esta conversa?')">
                    <input type="hidden" name="_token" value="{{ csrf_token('delete' ~ conversation.id) }}">
                    <button type="submit" class="btn btn-sm btn-outline-danger ms-3">Excluir</button>
                </form>
            </div>
        {% endfor %}
    </div>
{% endif %}

{% endblock %}
```

### 7.2 — `show.html.twig` — Tela do Chat (estilo ChatGPT)

Crie `templates/admin/conversation/show.html.twig`:

```twig
{% extends 'admin/admin-template.html.twig' %}

{% block title %}Chat — {{ client.name }}{% endblock %}

{% block content %}

<style>
    .chat-container {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 280px);
        min-height: 400px;
    }
    .chat-header {
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--bs-border-color);
        margin-bottom: 0.75rem;
        flex-shrink: 0;
    }
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 0.5rem 0;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .message-bubble {
        max-width: 80%;
        padding: 0.75rem 1rem;
        border-radius: 1rem;
        line-height: 1.5;
        font-size: 0.95rem;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .message-user {
        background-color: #0d6efd;
        color: #fff;
        margin-left: auto;
        border-bottom-right-radius: 0.25rem;
    }
    .message-assistant {
        background-color: #f0f0f0;
        color: #212529;
        margin-right: auto;
        border-bottom-left-radius: 0.25rem;
    }
    .message-wrapper {
        display: flex;
        flex-direction: column;
    }
    .message-wrapper.user { align-items: flex-end; }
    .message-wrapper.assistant { align-items: flex-start; }
    .message-meta {
        font-size: 0.72rem;
        color: #999;
        margin-top: 0.25rem;
        padding: 0 0.25rem;
    }
    .chat-input-area {
        flex-shrink: 0;
        padding-top: 0.75rem;
        border-top: 1px solid var(--bs-border-color);
        margin-top: 0.75rem;
    }
    .chat-input-area textarea {
        resize: none;
        border-radius: 1.5rem;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
    }
    .btn-send {
        border-radius: 50%;
        width: 42px;
        height: 42px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .typing-indicator {
        display: none;
        align-items: flex-start;
        gap: 0.5rem;
    }
    .typing-indicator span {
        background: #f0f0f0;
        color: #555;
        padding: 0.65rem 1rem;
        border-radius: 1rem;
        border-bottom-left-radius: 0.25rem;
        font-size: 0.9rem;
    }
</style>

<div class="chat-container">

    <div class="chat-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">💬 {{ conversation.title }}</h5>
            <div class="text-body-secondary small">{{ client.name }}</div>
        </div>
        <a href="{{ path('app_admin_conversation_index', {'clientId': client.id}) }}"
           class="btn btn-outline-secondary btn-sm">
            ← Conversas
        </a>
    </div>

    <div class="chat-messages" id="chatMessages">

        {% if messages is empty %}
            <div class="text-center text-body-secondary py-4" id="emptyState">
                <div style="font-size:2.5rem;">🤖</div>
                <p class="mt-1">Olá! Faça uma pergunta sobre <strong>{{ client.name }}</strong>.</p>
            </div>
        {% endif %}

        {% for message in messages %}
            <div class="message-wrapper {{ message.role }}">
                <div class="message-bubble message-{{ message.role }}">{{ message.content }}</div>
                <div class="message-meta">
                    {% if message.role == 'user' %}Você{% else %}IA{% endif %} ·
                    {{ message.createdAt ? message.createdAt|date('H:i') : '' }}
                    {% if message.role == 'assistant' and message.tokensUsed %}
                        · {{ message.tokensUsed }} tokens
                    {% endif %}
                </div>
            </div>
        {% endfor %}

        <div class="message-wrapper assistant typing-indicator" id="typingIndicator">
            <span>Digitando...</span>
        </div>

    </div>

    <div class="chat-input-area">
        <div class="d-flex gap-2 align-items-end">
            <textarea
                id="messageInput"
                class="form-control"
                rows="1"
                placeholder="Digite sua pergunta sobre {{ client.name }}..."
                style="max-height: 120px; overflow-y: auto;"
            ></textarea>
            <button type="button" class="btn btn-primary btn-send" id="sendBtn" title="Enviar">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11z"/>
                </svg>
            </button>
        </div>
        <div class="text-body-secondary small mt-1 ps-2">
            Pressione <kbd>Enter</kbd> para enviar, <kbd>Shift+Enter</kbd> para nova linha.
        </div>
    </div>

</div>

<script>
(function () {
    const chatMessages = document.getElementById('chatMessages');
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    const typingIndicator = document.getElementById('typingIndicator');
    const emptyState = document.getElementById('emptyState');

    // URL da rota que processa as mensagens
    const sendUrl = '{{ path('app_admin_conversation_message', {'clientId': client.id, 'id': conversation.id}) }}';

    // Rola para o final do chat
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    scrollToBottom();

    // Auto-resize do textarea conforme o usuário digita
    messageInput.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // Enter envia, Shift+Enter pula linha
    messageInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    sendBtn.addEventListener('click', sendMessage);

    function addMessage(role, content, meta) {
        if (emptyState) emptyState.style.display = 'none';

        const wrapper = document.createElement('div');
        wrapper.className = 'message-wrapper ' + role;

        const bubble = document.createElement('div');
        bubble.className = 'message-bubble message-' + role;
        bubble.textContent = content;

        const metaDiv = document.createElement('div');
        metaDiv.className = 'message-meta';
        metaDiv.textContent = meta;

        wrapper.appendChild(bubble);
        wrapper.appendChild(metaDiv);

        // Insere antes do indicador de digitação
        chatMessages.insertBefore(wrapper, typingIndicator);
        scrollToBottom();
    }

    function setLoading(loading) {
        sendBtn.disabled = loading;
        messageInput.disabled = loading;
        typingIndicator.style.display = loading ? 'flex' : 'none';
        if (loading) scrollToBottom();
    }

    function sendMessage() {
        const text = messageInput.value.trim();
        if (!text) return;

        const now = new Date().toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});

        // Exibe a mensagem do usuário imediatamente (sem esperar o servidor)
        addMessage('user', text, 'Você · ' + now);

        messageInput.value = '';
        messageInput.style.height = 'auto';
        setLoading(true);

        // Envia para o servidor via AJAX
        fetch(sendUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({message: text}),
        })
        .then(response => response.json())
        .then(data => {
            setLoading(false);
            if (data.success) {
                const tokenInfo = data.tokens ? ' · ' + data.tokens + ' tokens' : '';
                addMessage('assistant', data.message, 'IA · ' + now + tokenInfo);
            } else {
                addMessage('assistant', '❌ Erro: ' + (data.error ?? 'Erro desconhecido.'), 'IA · ' + now);
            }
        })
        .catch(err => {
            setLoading(false);
            addMessage('assistant', '❌ Erro de conexão. Tente novamente.', 'IA');
            console.error(err);
        });
    }

    messageInput.focus();
})();
</script>

{% endblock %}
```

---

## 🔘 PASSO 8 — Adicionar o Botão "Chat" na Lista de Clientes

Abra o arquivo `templates/admin/client/index.html.twig` e localize o bloco dos botões de ação de cada cliente:

```twig
{# Encontre esta parte: #}
<button type="button" class="btn btn-sm btn-outline-secondary"
onclick="location.href='{{ path('app_admin_client_edit', {'id': client.id}) }}'">
Editar
</button>
```

E adicione o botão "Chat" logo depois:

```twig
<button type="button" class="btn btn-sm btn-outline-success"
onclick="location.href='{{ path('app_admin_conversation_index', {'clientId': client.id}) }}'">
💬 Chat
</button>
```

---

## 🔗 PASSO 9 — (Opcional) Adicionar Link no Menu Lateral

Se quiser, também pode adicionar na página `show.html.twig` do cliente um link para as conversas:

```twig
<a href="{{ path('app_admin_conversation_index', {'clientId': client.id}) }}" class="btn btn-outline-primary">
    💬 Conversas com IA
</a>
```

---

## 🧪 PASSO 10 — Testar o Sistema

1. Certifique-se de ter configurado o `OPENAI_API_KEY` no `.env.local`
2. Rode a migration: `php bin/console doctrine:migrations:migrate`
3. Limpe o cache: `php bin/console cache:clear`
4. Acesse `https://127.0.0.1:8000/admin/client`
5. Clique em **💬 Chat** de qualquer cliente
6. Clique em **Nova Conversa**
7. Digite uma pergunta como: *"Quais projetos este cliente tem?"*
8. A resposta do GPT deve aparecer em alguns segundos

---

## 💡 Estratégia de Eficiência com Tokens

### Por que usamos `contextSnapshot`?

Sem pré-processamento, cada mensagem enviaria **todos os dados brutos** do cliente ao GPT, consumindo muitos tokens. Com o `contextSnapshot`:

- ✅ O contexto é **construído uma vez** ao iniciar a conversa
- ✅ É armazenado no banco — não precisamos buscar todos os dados a cada mensagem
- ✅ Limitamos o tamanho das transcrições e históricos mais antigos
- ✅ Usamos o modelo `gpt-4o-mini` que é 10x mais barato que o `gpt-4`

### Como a memória da conversa funciona?

O GPT não tem "memória" — a cada chamada você precisa enviar todo o histórico. O controller envia o histórico de mensagens anteriores + a nova pergunta. Para conversas muito longas, isso pode aumentar o custo. Uma melhoria futura seria limitar o histórico aos últimas N mensagens.

---

## 📝 Resumo dos Arquivos Criados/Modificados

| Ação | Arquivo |
|------|---------|
| ✅ CRIAR | `src/Entity/Conversation.php` |
| ✅ CRIAR | `src/Entity/ConversationMessage.php` |
| ✅ MODIFICAR | `src/Entity/Client.php` (adicionar relação) |
| ✅ CRIAR | `src/Repository/ConversationRepository.php` |
| ✅ CRIAR | `src/Repository/ConversationMessageRepository.php` |
| ✅ CRIAR | `src/Service/GptService.php` |
| ✅ CRIAR | `src/Service/ClientContextBuilder.php` |
| ✅ CRIAR | `src/Controller/admin/ConversationController.php` |
| ✅ CRIAR | `templates/admin/conversation/index.html.twig` |
| ✅ CRIAR | `templates/admin/conversation/show.html.twig` |
| ✅ MODIFICAR | `templates/admin/client/index.html.twig` (botão Chat) |
| ✅ MODIFICAR | `.env` (variáveis do OpenAI) |
| ✅ MODIFICAR | `.env.local` (chave real do OpenAI) |
| ✅ MODIFICAR | `config/services.yaml` (registrar GptService) |
| ✅ RODAR | `php bin/console doctrine:migrations:diff` |
| ✅ RODAR | `php bin/console doctrine:migrations:migrate` |
| ✅ RODAR | `php bin/console cache:clear` |
