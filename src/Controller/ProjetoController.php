<?php

namespace App\Controller;

use App\Entity\Projeto;
use App\Form\ProjetoType;
use App\Repository\ClienteRepository;
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
public function index(
    Request $request,
    ProjetoRepository $projetoRepository
): Response {
    $q = $request->query->get('q');

    if ($q) {
        $projetos = $projetoRepository->search($q);
    } else {
        $projetos = $projetoRepository->findAll();
    }

    return $this->render('projeto/index.html.twig', [
        'projetos' => $projetos,
    ]);
}


    #[Route('/novo', name: 'app_projeto_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        ClienteRepository $clienteRepository
    ): Response {
        $projeto = new Projeto();

        // Se vier cliente por query (?cliente=1), apenas seta no objeto
        $clienteId = $request->query->get('cliente');
        if ($clienteId) {
            $cliente = $clienteRepository->find($clienteId);
            if ($cliente) {
                $projeto->setCliente($cliente);
            }
        }
        
        $form = $this->createForm(ProjetoType::class, $projeto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($projeto);
            $em->flush();

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
    public function edit(
        Request $request,
        Projeto $projeto,
        EntityManagerInterface $em
    ): Response {
        
        
        $form = $this->createForm(ProjetoType::class, $projeto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_projeto_index');
        }

        return $this->render('projeto/edit.html.twig', [
            'projeto' => $projeto,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/cancelar', name: 'app_projeto_cancelar', methods: ['POST'])]
    public function cancelar(
        Projeto $projeto,
        EntityManagerInterface $em
    ): Response {
        $projeto->setStatus('cancelado');
        $projeto->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        return $this->redirectToRoute('app_projeto_index');
    }
}
