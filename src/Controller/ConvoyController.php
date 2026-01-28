<?php

namespace App\Controller;

use App\Repository\ConvoyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ConvoyController extends AbstractController
{
    #[Route('/convoy', name: 'app_convoy')]
    public function index(): Response
    {
        return $this->render('convoy/index.html.twig', [
            'controller_name' => 'ConvoyController',
        ]);
    }

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(ConvoyRepository $convoyRepository): Response
    {
        $convoys = $convoyRepository->findAll();
        return $this->json($convoys);
    }
}
