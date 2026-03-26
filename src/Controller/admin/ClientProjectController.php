<?php

namespace App\Controller\admin;

use App\Entity\ClientProject;
use App\Entity\ClientProjectAttached;
use App\Form\ClientProjectAttachedType;
use App\Form\ClientProjectType;
use App\Repository\ClientProjectAttachedRepository;
use App\Repository\ClientProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/client-project')]
final class ClientProjectController extends AbstractController
{
    #[Route('', name: 'app_admin_client_project_index', methods: ['GET'])]
    public function index(ClientProjectRepository $clientProjectRepository): Response
    {
        return $this->render('admin/client_project/index.html.twig', [
            'client_projects' => $clientProjectRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_client_project_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $clientProject = new ClientProject();
        $form = $this->createForm(ClientProjectType::class, $clientProject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($clientProject);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_client_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/client_project/new.html.twig', [
            'client_project' => $clientProject,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_client_project_show', methods: ['GET'])]
    public function show(ClientProject $clientProject): Response
    {
        return $this->render('admin/client_project/show.html.twig', [
            'client_project' => $clientProject,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_client_project_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        ClientProject $clientProject,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(ClientProjectType::class, $clientProject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_client_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/client_project/edit.html.twig', [
            'client_project' => $clientProject,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_client_project_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        ClientProject $clientProject,
        EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%/public/uploads/attached')] string $attachedDirectory,
        #[Autowire('%kernel.project_dir%/public/uploads/audioFile')] string $audioDirectory,
        #[Autowire('%kernel.project_dir%/public/uploads/project_logo')] string $projectLogoDirectory
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $clientProject->getId(), $request->request->getString('_token'))) {
            $this->removeFileIfExists($projectLogoDirectory, $clientProject->getLogoFilename());

            foreach ($clientProject->getClientProjectAttacheds() as $attachment) {
                $this->removeFileIfExists($attachedDirectory, $attachment->getFile());
            }

            foreach ($clientProject->getClientProjectHistories() as $history) {
                $this->removeFileIfExists($audioDirectory, $history->getAudioFilename());
            }

            $entityManager->remove($clientProject);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_client_project_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/attachment-new', name: 'app_admin_client_project_attachment_add', methods: ['GET', 'POST'])]
    public function newAttachment(
        ClientProject $clientProject,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/attached')] string $attachedDirectory
    ): Response {
        $clientProjectAttached = new ClientProjectAttached();
        $form = $this->createForm(ClientProjectAttachedType::class, $clientProjectAttached);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile[]|UploadedFile|null $attachedFiles */
            $attachedFiles = $form->get('file')->getData();

            if ($attachedFiles instanceof UploadedFile) {
                $attachedFiles = [$attachedFiles];
            }

            if (!is_array($attachedFiles) || count($attachedFiles) === 0) {
                $this->addFlash('warning', 'Nenhum arquivo foi enviado.');

                return $this->render('admin/client_project/attachment_form.html.twig', [
                    'clientProject' => $clientProject,
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
                    $attachedFile->move($attachedDirectory, $newFilename);

                    $newAttachment = new ClientProjectAttached();
                    $newAttachment->setProjeto($clientProject);
                    $newAttachment->setFile($newFilename);
                    $newAttachment->setDescription($clientProjectAttached->getDescription());

                    $entityManager->persist($newAttachment);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Não foi possível enviar o anexo: ' . $attachedFile->getClientOriginalName());
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute(
                'app_admin_client_project_show',
                ['id' => $clientProject->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('admin/client_project/attachment_form.html.twig', [
            'clientProject' => $clientProject,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/attachment/{attachedId}/delete', name: 'app_admin_client_project_attachment_delete', methods: ['POST'])]
    public function deleteAttachment(
        ClientProject $clientProject,
        int $attachedId,
        Request $request,
        EntityManagerInterface $entityManager,
        ClientProjectAttachedRepository $clientProjectAttachedRepository,
        #[Autowire('%kernel.project_dir%/public/uploads/attached')] string $attachedDirectory
    ): Response {
        if (!$this->isCsrfTokenValid('delete_attachment_' . $attachedId, $request->request->getString('_token'))) {
            return $this->redirectToRoute(
                'app_admin_client_project_show',
                ['id' => $clientProject->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        $attachment = $clientProjectAttachedRepository->find($attachedId);

        if ($attachment && $attachment->getProjeto()?->getId() === $clientProject->getId()) {
            $this->removeFileIfExists($attachedDirectory, $attachment->getFile());

            $entityManager->remove($attachment);
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'app_admin_client_project_show',
            ['id' => $clientProject->getId()],
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