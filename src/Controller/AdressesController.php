<?php

namespace App\Controller;

use App\Repository\AdressesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdressesController extends AbstractController
{
    #[Route('/adresses', name: 'app_adresses')]
    public function index(): Response
    {
        return $this->render('adresses/index.html.twig', [
            'controller_name' => 'AdressesController',
        ]);
    }

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(AdressesRepository $adressesRepository): Response
    {
        $adresses = $adressesRepository->findAll();
        return $this->json($adresses);
    }
}
