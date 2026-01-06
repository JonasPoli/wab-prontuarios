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
    public function index(RegistroHistoricoRepository $repository): Response
    {
        return $this->render('registro_historico/index.html.twig', [
            'registros' => $repository->findBy(
                [],
                ['dataRegistro' => 'DESC']
            ),
        ]);
    }

    #[Route('/projeto/{id}/novo', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Projeto $projeto,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        
        $registro = new RegistroHistorico();
        $registro->setProjeto($projeto);

        if ($this->getUser()) {
            $registro->setUsuarioAutor($this->getUser());
        }

        $form = $this->createForm(RegistroHistoricoType::class, $registro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($registro);
            $em->flush();

            return $this->redirectToRoute('app_projeto_show', [
                'id' => $projeto->getId(),
            ]);
        }

        return $this->render('registro_historico/new.html.twig', [
            'form' => $form,
            'projeto' => $projeto,
        ]);
    }

    // criar uma rota para capturar o historico

            // localizar todos os anexos do historico

                // twig da lista de anexos
}
