<?php

namespace App\Controller;

use App\Entity\Events;
use App\Entity\Region;
use App\Form\EventsType;
use App\Repository\EventsRepository;
use App\Repository\RegionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventsController extends AbstractController
{
    #[Route('/events', name: 'app_events', methods: ['GET'])]
    public function index(Request $request, EventsRepository $eventsRepository, RegionRepository $regionRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $region = null;
        $regionId = $request->query->get('region');
        if (is_string($regionId) && ctype_digit($regionId)) {
            $region = $regionRepository->find((int) $regionId);
        }

        $events = $region instanceof Region
            ? $eventsRepository->findBy(['regionID' => $region], ['Date' => 'ASC'])
            : $eventsRepository->findBy([], ['Date' => 'ASC']);

        return $this->render('events/index.html.twig', [
            'events' => $events,
            'region' => $region,
        ]);
    }

    #[Route('/events/{id}', name: 'app_events_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(Events $event): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('events/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/events/new', name: 'app_events_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EVENT_MANAGER');

        $event = new Events();
        $event->setCreatedAt(new \DateTimeImmutable());
        $event->setRatingAverage('0');

        $form = $this->createForm(EventsType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$event->getRatingAverage()) {
                $event->setRatingAverage('0');
            }

            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Événement créé.');
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        return $this->render('events/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/events/{id}/edit', name: 'app_events_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Events $event, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EVENT_MANAGER');

        $form = $this->createForm(EventsType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$event->getRatingAverage()) {
                $event->setRatingAverage('0');
            }

            $em->flush();
            $this->addFlash('success', 'Événement modifié.');

            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        return $this->render('events/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/events/{id}/delete', name: 'app_events_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(Request $request, Events $event, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EVENT_MANAGER');

        if (!$this->isCsrfTokenValid('delete_event_' . $event->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        $em->remove($event);
        $em->flush();
        $this->addFlash('success', 'Événement supprimé.');

        return $this->redirectToRoute('app_home');
    }
}
