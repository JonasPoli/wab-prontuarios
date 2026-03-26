<?php

namespace App\Controller\admin;

use App\Entity\ClientProjectAttached;
use App\Form\ClientProjectAttachedType;
use App\Repository\ClientProjectAttachedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/client/project/attached')]
final class ClientProjectAttachedController extends AbstractController
{
    #[Route(name: 'app_admin_client_project_attached_index', methods: ['GET'])]
    public function index(ClientProjectAttachedRepository $clientProjectAttachedRepository): Response
    {
        return $this->render('admin/client_project_attached/index.html.twig', [
            'client_project_attacheds' => $clientProjectAttachedRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_client_project_attached_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $clientProjectAttached = new ClientProjectAttached();
        $form = $this->createForm(ClientProjectAttachedType::class, $clientProjectAttached);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($clientProjectAttached);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_client_project_attached_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/client_project_attached/new.html.twig', [
            'client_project_attached' => $clientProjectAttached,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_client_project_attached_show', methods: ['GET'])]
    public function show(ClientProjectAttached $clientProjectAttached): Response
    {
        return $this->render('admin/client_project_attached/show.html.twig', [
            'client_project_attached' => $clientProjectAttached,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_client_project_attached_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ClientProjectAttached $clientProjectAttached, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClientProjectAttachedType::class, $clientProjectAttached);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_client_project_attached_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/client_project_attached/edit.html.twig', [
            'client_project_attached' => $clientProjectAttached,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_client_project_attached_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, ClientProjectAttached $clientProjectAttached, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$clientProjectAttached->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($clientProjectAttached);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_client_project_attached_index', [], Response::HTTP_SEE_OTHER);
    }
}
