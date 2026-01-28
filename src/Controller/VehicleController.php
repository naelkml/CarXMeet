<?php

namespace App\Controller;

use App\Repository\VehicleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VehicleController extends AbstractController
{
    #[Route('/vehicle', name: 'app_vehicle')]
    public function index(): Response
    {
        return $this->render('vehicle/index.html.twig', [
            'controller_name' => 'VehicleController',
        ]);
    }

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(VehicleRepository $vehicleRepository): Response
    {
        $vehicles = $vehicleRepository->findAll();
        return $this->json($vehicles);
    }
}
