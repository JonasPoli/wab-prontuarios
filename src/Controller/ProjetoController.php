<?php

namespace App\Controller;

use App\Entity\Projeto;
use App\Form\ProjetoType;
use App\Repository\ProjetoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projeto')]
class ProjetoController extends AbstractController
{
    #[Route('/', name: 'app_projeto_index', methods: ['GET'])]
    public function index(ProjetoRepository $projetoRepository): Response
    {
        return $this->render('projeto/index.html.twig', [
            'projetos' => $projetoRepository->findAll(),
        ]);
    }

    #[Route('/novo', name: 'app_projeto_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $projeto = new Projeto();
        $form = $this->createForm(ProjetoType::class, $projeto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projeto);
            $entityManager->flush();

            return $this->redirectToRoute('app_projeto_index');
        }

        return $this->render('projeto/new.html.twig', [
            'projeto' => $projeto,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_projeto_show', methods: ['GET'])]
    public function show(Projeto $projeto): Response
    {
        return $this->render('projeto/show.html.twig', [
            'projeto' => $projeto,
        ]);
    }

    #[Route('/{id}/editar', name: 'app_projeto_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Projeto $projeto, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjetoType::class, $projeto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_projeto_show', [
                'id' => $projeto->getId(),
            ]);
        }

        return $this->render('projeto/edit.html.twig', [
            'projeto' => $projeto,
            'form' => $form,
        ]);
    }

    /**
     * ✅ CANCELAR PROJETO (MUDA STATUS)
     */
    #[Route('/{id}/cancelar', name: 'app_projeto_cancel', methods: ['POST'])]
    public function cancelar(
        Request $request,
        Projeto $projeto,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'cancelar' . $projeto->getId(),
            $request->request->get('_token')
        )) {
            $projeto->setStatus('cancelado');
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_projeto_show', [
            'id' => $projeto->getId(),
        ]);
    }
}
