<?php

namespace App\Controller\admin;

use App\Entity\ClientProjectHistory;
use App\Form\ClientProjectHistoryType;
use App\Repository\ClientProjectHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/client-project-history')]
final class ClientProjectHistoryController extends AbstractController
{
    #[Route(name: 'app_admin_client_project_history_index', methods: ['GET'])]
    public function index(ClientProjectHistoryRepository $clientProjectHistoryRepository): Response
    {
        return $this->render('admin/client_project_history/index.html.twig', [
            'client_project_histories' => $clientProjectHistoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_client_project_history_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/audioFile')] string $audioDirectory
    ): Response
    {
        $clientProjectHistory = new ClientProjectHistory();
        $form = $this->createForm(ClientProjectHistoryType::class, $clientProjectHistory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $audioFile */
            $audioFile = $form->get('audioFile')->getData();

            if ($audioFile) {
                $originalFilename = pathinfo($audioFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $audioFile->guessExtension() ?: $audioFile->getClientOriginalExtension() ?: 'bin';

                $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

                try {
                    $audioFile->move($audioDirectory, $newFilename);
                    $clientProjectHistory->setAudioFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Não foi possível enviar o áudio.');
                }
            }

            $entityManager->persist($clientProjectHistory);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_client_project_history_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/client_project_history/new.html.twig', [
            'client_project_history' => $clientProjectHistory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_client_project_history_show', methods: ['GET'])]
    public function show(ClientProjectHistory $clientProjectHistory): Response
    {
        return $this->render('admin/client_project_history/show.html.twig', [
            'client_project_history' => $clientProjectHistory,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_client_project_history_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        ClientProjectHistory $clientProjectHistory,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/audioFile')] string $audioDirectory
    ): Response
    {
        $form = $this->createForm(ClientProjectHistoryType::class, $clientProjectHistory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $audioFile */
            $audioFile = $form->get('audioFile')->getData();

            if ($audioFile) {
                $oldFilename = $clientProjectHistory->getAudioFilename();

                $originalFilename = pathinfo($audioFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $audioFile->guessExtension() ?: $audioFile->getClientOriginalExtension() ?: 'bin';

                $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

                try {
                    $audioFile->move($audioDirectory, $newFilename);

                    if ($oldFilename) {
                        $oldPath = rtrim($audioDirectory, '/') . '/' . $oldFilename;

                        //apagar arquivo de áudio associado
                        if (is_file($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    $clientProjectHistory->setAudioFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Não foi possível enviar o áudio.');
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_admin_client_project_history_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/client_project_history/edit.html.twig', [
            'client_project_history' => $clientProjectHistory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_client_project_history_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        ClientProjectHistory $clientProjectHistory,
        EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%/public/uploads/audioFile')] string $audioDirectory
    ): Response
    {
        if ($this->isCsrfTokenValid('delete' . $clientProjectHistory->getId(), $request->getPayload()->getString('_token'))) {
            $audioFilename = $clientProjectHistory->getAudioFilename();

            if ($audioFilename) {
                $audioPath = rtrim($audioDirectory, '/') . '/' . $audioFilename;

                //apagar arquivo de áudio associado
                if (is_file($audioPath)) {
                    unlink($audioPath);
                }
            }

            $entityManager->remove($clientProjectHistory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_client_project_history_index', [], Response::HTTP_SEE_OTHER);
    }
}