<?php

namespace App\Controller;

use App\Entity\RegistroHistoricoAnexo;
use App\Form\RegistroHistoricoAnexoType;
use App\Repository\RegistroHistoricoAnexoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/registro/historico/anexo')]
final class RegistroHistoricoAnexoController extends AbstractController
{
    #[Route(name: 'app_registro_historico_anexo_index', methods: ['GET'])]
    public function index(RegistroHistoricoAnexoRepository $registroHistoricoAnexoRepository): Response
    {
        return $this->render('registro_historico_anexo/index.html.twig', [
            'registro_historico_anexos' => $registroHistoricoAnexoRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_registro_historico_anexo_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $registroHistoricoAnexo = new RegistroHistoricoAnexo();
        $form = $this->createForm(RegistroHistoricoAnexoType::class, $registroHistoricoAnexo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($registroHistoricoAnexo);
            $entityManager->flush();

            return $this->redirectToRoute('app_registro_historico_anexo_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('registro_historico_anexo/new.html.twig', [
            'registro_historico_anexo' => $registroHistoricoAnexo,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_registro_historico_anexo_show', methods: ['GET'])]
    public function show(RegistroHistoricoAnexo $registroHistoricoAnexo): Response
    {
        return $this->render('registro_historico_anexo/show.html.twig', [
            'registro_historico_anexo' => $registroHistoricoAnexo,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_registro_historico_anexo_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RegistroHistoricoAnexo $registroHistoricoAnexo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RegistroHistoricoAnexoType::class, $registroHistoricoAnexo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_registro_historico_anexo_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('registro_historico_anexo/edit.html.twig', [
            'registro_historico_anexo' => $registroHistoricoAnexo,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_registro_historico_anexo_delete', methods: ['POST'])]
    public function delete(Request $request, RegistroHistoricoAnexo $registroHistoricoAnexo, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$registroHistoricoAnexo->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($registroHistoricoAnexo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_registro_historico_anexo_index', [], Response::HTTP_SEE_OTHER);
    }
}
