<?php

namespace App\Controller;

use App\Repository\EventsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventsController extends AbstractController
{
    #[Route('/events', name: 'app_events')]
    public function index(): Response
    {
        return $this->render('events/index.html.twig', [
            'controller_name' => 'EventsController',
        ]);
    }

    //Méthode pour lister les évènements TEST
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(EventsRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        return $this->json($events);
    }
}

