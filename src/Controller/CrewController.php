<?php

namespace App\Controller;

use App\Repository\CrewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CrewController extends AbstractController
{
    #[Route('/crew', name: 'app_crew')]
    public function index(): Response
    {
        return $this->render('crew/index.html.twig', [
            'controller_name' => 'CrewController',
        ]);
    }

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(CrewRepository $crewRepository): Response
    {
        $crews = $crewRepository->findAll();
        return $this->json($crews);
    }
}
