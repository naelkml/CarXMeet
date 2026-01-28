<?php

namespace App\Controller;

use App\Repository\RegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RegionController extends AbstractController
{
    #[Route('/region', name: 'app_region')]
    public function index(): Response
    {
        return $this->render('region/index.html.twig', [
            'controller_name' => 'RegionController',
        ]);
    }

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(RegionRepository $regionRepository): Response
    {
        $regions = $regionRepository->findAll();
        return $this->json($regions);
    }
}
