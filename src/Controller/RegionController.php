<?php

namespace App\Controller;

use App\Repository\RegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RegionController extends AbstractController
{
    #[Route('/regions', name: 'app_regions', methods: ['GET'])]
    public function index(RegionRepository $regionRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('region/index.html.twig', [
            'regions' => $regionRepository->findBy([], ['name' => 'ASC']),
        ]);
    }
}

