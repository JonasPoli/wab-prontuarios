<?php

namespace App\Controller;

use App\Entity\RegistroHistorico;
use App\Form\RegistroHistoricoType;
use App\Repository\RegistroHistoricoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/registro-historico')]
class RegistroHistoricoController extends AbstractController
{
    #[Route('/', name: 'app_registro_historico_index', methods: ['GET'])]
    public function index(RegistroHistoricoRepository $registroHistoricoRepository): Response
    {
        return $this->render('registro_historico/index.html.twig', [
            'registros' => $registroHistoricoRepository->findAll(),
        ]);
    }

    #[Route('/novo', name: 'app_registro_historico_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $registro = new RegistroHistorico();
        $form = $this->createForm(RegistroHistoricoType::class, $registro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($registro);
            $entityManager->flush();

            return $this->redirectToRoute('app_registro_historico_index');
        }

        return $this->render('registro_historico/new.html.twig', [
            'registro' => $registro,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_registro_historico_show', methods: ['GET'])]
    public function show(RegistroHistorico $registro): Response
    {
        return $this->render('registro_historico/show.html.twig', [
            'registro' => $registro,
        ]);
    }

    #[Route('/{id}/editar', name: 'app_registro_historico_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RegistroHistorico $registro, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RegistroHistoricoType::class, $registro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_registro_historico_index');
        }

        return $this->render('registro_historico/edit.html.twig', [
            'registro' => $registro,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_registro_historico_delete', methods: ['POST'])]
    public function delete(Request $request, RegistroHistorico $registro, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$registro->getId(), $request->request->get('_token'))) {
            $entityManager->remove($registro);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_registro_historico_index');
    }
}
