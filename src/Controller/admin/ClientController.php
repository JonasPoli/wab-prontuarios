<?php

namespace App\Controller\admin;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/client')]
final class ClientController extends AbstractController
{
    #[Route(name: 'app_admin_client_index', methods: ['GET'])]
    public function index(ClientRepository $clientRepository): Response
    {
        return $this->render('admin/client/index.html.twig', [
            'clients' => $clientRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_client_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/logoFile')] string $logoDirectory
    ): Response {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $logoFile */
            $logoFile = $form->get('logoFilename')->getData();

            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $logoFile->guessExtension() ?: $logoFile->getClientOriginalExtension() ?: 'bin';
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

                try {
                    $logoFile->move($logoDirectory, $newFilename);
                    $client->setLogoFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Não foi possível enviar a logo.');
                }
            }

            $entityManager->persist($client);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_client_index');
        }

        return $this->render('admin/client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_client_show', methods: ['GET'])]
    public function show(Client $client): Response
    {
        return $this->render('admin/client/show.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_client_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Client $client,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/logoFile')] string $logoDirectory
    ): Response {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $logoFile */
            $logoFile = $form->get('logoFilename')->getData();

            if ($logoFile) {
                $oldFilename = $client->getLogoFilename();

                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $logoFile->guessExtension() ?: $logoFile->getClientOriginalExtension() ?: 'bin';
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

                try {
                    $logoFile->move($logoDirectory, $newFilename);

                    if ($oldFilename) {
                        $oldPath = rtrim($logoDirectory, '/') . '/' . $oldFilename;

                        if (is_file($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    $client->setLogoFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Não foi possível enviar a logo.');
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_admin_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $client->getId(), $request->request->getString('_token'))) {
            $clientLogoDir = $this->getParameter('kernel.project_dir') . '/public/uploads/logoFile/';
            $attachedDir = $this->getParameter('kernel.project_dir') . '/public/uploads/attached/';
            $audioDir = $this->getParameter('kernel.project_dir') . '/public/uploads/audioFile/';

            $this->removeFileIfExists($clientLogoDir, $client->getLogoFilename());

            foreach ($client->getClientProjects() as $project) {
                foreach ($project->getClientProjectAttacheds() as $attached) {
                    $this->removeFileIfExists($attachedDir, $attached->getFile());
                }

                foreach ($project->getClientProjectHistories() as $history) {
                    $this->removeFileIfExists($audioDir, $history->getAudioFilename());
                }
            }

            $entityManager->remove($client);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_client_index', [], Response::HTTP_SEE_OTHER);
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