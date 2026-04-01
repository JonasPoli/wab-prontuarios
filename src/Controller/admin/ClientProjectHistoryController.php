<?php

namespace App\Controller\admin;

use App\Entity\ClientProjectHistory;
use App\Entity\ClientProjectHistoryAttached;
use App\Form\ClientProjectHistoryAttachedType;
use App\Form\ClientProjectHistoryType;
use App\Repository\ClientProjectHistoryAttachedRepository;
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
    $histories = $clientProjectHistoryRepository->findBy([], [
        'occurredAt' => 'DESC',
        'id' => 'DESC',
    ]);

    $groupedHistories = [];

    foreach ($histories as $history) {
        $project = $history->getClientProject();

        if (!$project) {
            $groupedHistories['sem_projeto']['project'] = null;
            $groupedHistories['sem_projeto']['items'][] = $history;
            continue;
        }

        $key = (string) $project->getId();

        if (!isset($groupedHistories[$key])) {
            $groupedHistories[$key] = [
                'project' => $project,
                'items' => [],
            ];
        }

        $groupedHistories[$key]['items'][] = $history;
    }

    return $this->render('admin/client_project_history/index.html.twig', [
        'grouped_histories' => $groupedHistories,
    ]);
}

    #[Route('/new', name: 'app_admin_client_project_history_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/audioFile')] string $audioDirectory
    ): Response {
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
            'form' => $form->createView(),
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
    ): Response {
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
                        $oldPath = rtrim($audioDirectory, '/') . '/' . ltrim($oldFilename, '/');

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
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_client_project_history_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        ClientProjectHistory $clientProjectHistory,
        EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%/public/uploads/audioFile')] string $audioDirectory,
        #[Autowire('%kernel.project_dir%/public/uploads/history_attached')] string $historyAttachedDirectory
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $clientProjectHistory->getId(), $request->request->getString('_token'))) {
            $this->removeFileIfExists($audioDirectory, $clientProjectHistory->getAudioFilename());

            foreach ($clientProjectHistory->getClientProjectHistoryAttacheds() as $attachment) {
                $this->removeFileIfExists($historyAttachedDirectory, $attachment->getFile());
            }

            $entityManager->remove($clientProjectHistory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_client_project_history_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/attachment-new', name: 'app_admin_client_project_history_attachment_add', methods: ['GET', 'POST'])]
    public function newAttachment(
        ClientProjectHistory $clientProjectHistory,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/history_attached')] string $historyAttachedDirectory
    ): Response {
        $clientProjectHistoryAttached = new ClientProjectHistoryAttached();
        $form = $this->createForm(ClientProjectHistoryAttachedType::class, $clientProjectHistoryAttached);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile[]|UploadedFile|null $attachedFiles */
            $attachedFiles = $form->get('file')->getData();

            if ($attachedFiles instanceof UploadedFile) {
                $attachedFiles = [$attachedFiles];
            }

            if (!is_array($attachedFiles) || count($attachedFiles) === 0) {
                $this->addFlash('warning', 'Nenhum arquivo foi enviado.');

                return $this->render('admin/client_project_history/attachment_form.html.twig', [
                    'clientProjectHistory' => $clientProjectHistory,
                    'form' => $form->createView(),
                ]);
            }

            foreach ($attachedFiles as $attachedFile) {
                if (!$attachedFile instanceof UploadedFile) {
                    continue;
                }

                $originalFilename = pathinfo($attachedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $attachedFile->guessExtension() ?: $attachedFile->getClientOriginalExtension() ?: 'bin';
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

                try {
                    $attachedFile->move($historyAttachedDirectory, $newFilename);

                    $newAttachment = new ClientProjectHistoryAttached();
                    $newAttachment->setClientProjectHistory($clientProjectHistory);
                    $newAttachment->setFile($newFilename);
                    $newAttachment->setDescription($clientProjectHistoryAttached->getDescription());

                    $entityManager->persist($newAttachment);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Não foi possível enviar o anexo: ' . $attachedFile->getClientOriginalName());
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute(
                'app_admin_client_project_history_show',
                ['id' => $clientProjectHistory->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('admin/client_project_history/attachment_form.html.twig', [
            'clientProjectHistory' => $clientProjectHistory,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/attachment/{attachedId}/delete', name: 'app_admin_client_project_history_attachment_delete', methods: ['POST'])]
    public function deleteAttachment(
        ClientProjectHistory $clientProjectHistory,
        int $attachedId,
        Request $request,
        EntityManagerInterface $entityManager,
        ClientProjectHistoryAttachedRepository $clientProjectHistoryAttachedRepository,
        #[Autowire('%kernel.project_dir%/public/uploads/history_attached')] string $historyAttachedDirectory
    ): Response {
        if (!$this->isCsrfTokenValid('delete_attachment_' . $attachedId, $request->request->getString('_token'))) {
            return $this->redirectToRoute(
                'app_admin_client_project_history_show',
                ['id' => $clientProjectHistory->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        $attachment = $clientProjectHistoryAttachedRepository->find($attachedId);

        if ($attachment && $attachment->getClientProjectHistory()?->getId() === $clientProjectHistory->getId()) {
            $this->removeFileIfExists($historyAttachedDirectory, $attachment->getFile());

            $entityManager->remove($attachment);
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'app_admin_client_project_history_show',
            ['id' => $clientProjectHistory->getId()],
            Response::HTTP_SEE_OTHER
        );
    }

    private function removeFileIfExists(string $directory, ?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $path = rtrim($directory, '/') . '/' . ltrim($filename, '/');

        if (is_file($path)) {
            unlink($path);
        }
    }
}