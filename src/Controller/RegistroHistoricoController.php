<?php

namespace App\Controller;

use App\Entity\Projeto;
use App\Entity\RegistroHistorico;
use App\Form\RegistroHistoricoType;
use App\Repository\RegistroHistoricoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/registro-historico', name: 'app_registro_historico_')]
class RegistroHistoricoController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(RegistroHistoricoRepository $registroHistoricoRepository): Response
    {
        return $this->render('registro_historico/index.html.twig', [
            'registros' => $registroHistoricoRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Projeto $projeto,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $registroHistorico = new RegistroHistorico();
        $registroHistorico->setProjeto($projeto);

        if ($this->getUser()) {
            $registroHistorico->setUsuarioAutor($this->getUser());
        }

        $form = $this->createForm(RegistroHistoricoType::class, $registroHistorico);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($registroHistorico);
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_projeto_show',
                ['id' => $projeto->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('registro_historico/new.html.twig', [
            'form' => $form,
            'projeto' => $projeto,
        ]);
    }
}
