<?php

namespace App\Controller;

use App\Entity\Convoy;
use App\Entity\ConvoyParticipation;
use App\Entity\Events;
use App\Entity\User;
use App\Form\ConvoyType;
use App\Repository\ConvoyParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/events/{id}/convoys/new', name: 'app_convoy_new', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function new(Request $request, Events $event, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $convoy = new Convoy();
        $convoy->setEventID($event);

        $form = $this->createForm(ConvoyType::class, $convoy);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($convoy);
            $em->flush();

            $this->addFlash('success', 'Convoi créé.');
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        return $this->render('convoy/new.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/convoys/{id}/join', name: 'app_convoy_join', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function join(Request $request, Convoy $convoy, ConvoyParticipationRepository $convoyParticipationRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        if (!$this->isCsrfTokenValid('join_convoy_' . $convoy->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_events_show', ['id' => $convoy->getEventID()?->getId()]);
        }

        $existing = $convoyParticipationRepository->findOneForUser($convoy, $user);
        if ($existing) {
            $this->addFlash('info', 'Tu es déjà dans ce convoi.');
            return $this->redirectToRoute('app_events_show', ['id' => $convoy->getEventID()?->getId()]);
        }

        $member = new ConvoyParticipation();
        $member->setConvoyID($convoy);
        $member->setUserID($user);

        $em->persist($member);
        $em->flush();

        $this->addFlash('success', 'Tu as rejoint le convoi.');
        return $this->redirectToRoute('app_events_show', ['id' => $convoy->getEventID()?->getId()]);
    }

    #[Route('/convoys/{id}/leave', name: 'app_convoy_leave', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function leave(Request $request, Convoy $convoy, ConvoyParticipationRepository $convoyParticipationRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        if (!$this->isCsrfTokenValid('leave_convoy_' . $convoy->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_events_show', ['id' => $convoy->getEventID()?->getId()]);
        }

        $existing = $convoyParticipationRepository->findOneForUser($convoy, $user);
        if (!$existing) {
            $this->addFlash('info', 'Tu ne fais pas partie de ce convoi.');
            return $this->redirectToRoute('app_events_show', ['id' => $convoy->getEventID()?->getId()]);
        }

        $em->remove($existing);
        $em->flush();

        $this->addFlash('success', 'Tu as quitté le convoi.');
        return $this->redirectToRoute('app_events_show', ['id' => $convoy->getEventID()?->getId()]);
    }
}
