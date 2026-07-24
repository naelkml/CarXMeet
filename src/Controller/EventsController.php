<?php
/*
|--------------------------------------------------------------------------
| CONTROLEUR DES ÉVÉNEMENTS
|--------------------------------------------------------------------------
|
| Ce contrôleur gère toutes les actions liées aux événements de
| l'application CarXMeet.
|
| Un contrôleur est une classe qui reçoit les requêtes envoyées par
| l'utilisateur (clic sur un bouton, ouverture d'une page, envoi d'un
| formulaire...) puis effectue les traitements nécessaires avant de
| renvoyer une réponse.
|
| Les principales fonctionnalités de ce contrôleur sont :
|
| - Afficher la liste de tous les événements.
| - Afficher le détail d'un événement.
| - Créer un nouvel événement.
| - Modifier un événement existant.
| - Permettre à un utilisateur de participer à un événement.
| - Permettre à un utilisateur de quitter un événement.
| - Permettre aux utilisateurs de noter un événement.
| - Gérer les photos d'un événement.
| - Supprimer un événement via l'API pour les administrateurs.
|
| Les données sont enregistrées et récupérées grâce à Doctrine,
| l'ORM utilisé par Symfony pour communiquer avec la base de données.
|
| Le contrôleur utilise également différents Repository afin de
| rechercher des informations comme :
| - les événements,
| - les participations,
| - les notes,
| - les convois,
| - les amis,
| - les régions.
|
| Plusieurs vérifications de sécurité sont présentes :
| - contrôle des rôles (ROLE_USER, ROLE_EVENT_MANAGER),
| - vérification des jetons CSRF lors des formulaires,
| - contrôle des permissions avant certaines actions.
|
| Après chaque action importante (création, modification, suppression,
| participation...), un message temporaire (Flash Message) est affiché
| afin d'informer l'utilisateur du résultat de son action.
|
*/

namespace App\Controller;

use App\Entity\Participation;
use App\Entity\EventPhoto;
use App\Entity\EventRating;
use App\Entity\Events;
use App\Entity\Region;
use App\Entity\User;
use App\Form\EventsType;
use App\Repository\ConvoyParticipationRepository;
use App\Repository\EventRatingRepository;
use App\Repository\EventsRepository;
use App\Repository\FriendshipRepository;
use App\Repository\RegionRepository;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventsController extends AbstractController
{
    // Affiche la liste des événements.
    // L'utilisateur peut également filtrer les événements selon une région ou les trier suivant différents critères.
    #[Route('/events', name: 'app_events', methods: ['GET'])]
    public function index(Request $request, EventsRepository $eventsRepository, RegionRepository $regionRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $region = null;
        $regionId = $request->query->get('region');
        if (is_string($regionId) && ctype_digit($regionId)) {
            $region = $regionRepository->find((int) $regionId);
        }

        $sort = $request->query->get('sort');
        $sort = is_string($sort) ? $sort : 'date_asc';
        $events = $eventsRepository->findForIndex($region instanceof Region ? $region : null, $sort);

        return $this->render('events/index.html.twig', [
            'events' => $events,
            'region' => $region,
            'sort' => $sort,
        ]);
    }

    // Affiche toutes les informations d'un événement.
    // Cette méthode récupère également les participants,
    // les convois, les notes et les informations permettant
    // de savoir si l'utilisateur participe déjà à l'événement.
    #[Route('/events/{id}', name: 'app_events_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(
        Events $event,
        EventRatingRepository $eventRatingRepository,
        ParticipationRepository $participationRepository,
        FriendshipRepository $friendshipRepository,
        ConvoyParticipationRepository $convoyParticipationRepository,
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $myRating = null;
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        $myRating = $eventRatingRepository->findOneForUser($event, $user);

        $isParticipating = $participationRepository->findOneForUser($event, $user) !== null;

        $participantViews = [];
        foreach ($participationRepository->findParticipantsForEvent($event) as $participation) {
            $participantUser = $participation->getUserID();
            if (!$participantUser instanceof User) {
                continue;
            }

            $participantViews[] = [
                'user' => $participantUser,
                'isMe' => $participantUser->getId() === $user->getId(),
                'isFriend' => $friendshipRepository->areFriends($user, $participantUser),
            ];
        }

        $convoyViews = [];
        foreach ($event->getConvoys() as $convoy) {
            $isMember = $convoyParticipationRepository->findOneForUser($convoy, $user) !== null;

            $memberViews = [];
            foreach ($convoyParticipationRepository->findMembersForConvoy($convoy) as $membership) {
                $memberUser = $membership->getUserID();
                if (!$memberUser instanceof User) {
                    continue;
                }

                $memberViews[] = [
                    'user' => $memberUser,
                    'isMe' => $memberUser->getId() === $user->getId(),
                    'isFriend' => $friendshipRepository->areFriends($user, $memberUser),
                ];
            }

            $convoyViews[] = [
                'convoy' => $convoy,
                'isMember' => $isMember,
                'members' => $memberViews,
            ];
        }

        return $this->render('events/show.html.twig', [
            'event' => $event,
            'myRating' => $myRating,
            'isParticipating' => $isParticipating,
            'participants' => $participantViews,
            'convoys' => $convoyViews,
        ]);
    }

    // Permet à un utilisateur de s'inscrire à un événement.
    // Avant l'inscription, plusieurs vérifications sont effectuées :
    // l'utilisateur doit être connecté,
    // le jeton CSRF doit être valide,
    // l'utilisateur ne doit pas déjà participer.
    #[Route('/events/{id}/participate', name: 'app_events_participate', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function participate(
        Request $request,
        Events $event,
        ParticipationRepository $participationRepository,
        EntityManagerInterface $em,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        if (!$this->isCsrfTokenValid('participate_event_' . $event->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        $existing = $participationRepository->findOneForUser($event, $user);
        if ($existing) {
            $this->addFlash('info', 'Tu participes déjà à cet événement.');
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        $participation = new Participation();
        $participation->setEventID($event);
        $participation->setUserID($user);
        $participation->setJoinedAt(new \DateTimeImmutable());

        $em->persist($participation);
        $em->flush();

        $this->addFlash('success', 'Participation enregistrée.');
        return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
    }

    // Permet à un utilisateur de quitter un événement.
    // Si l'utilisateur participe déjà, sa participation
    // est supprimée de la base de données.
    #[Route('/events/{id}/leave', name: 'app_events_leave', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function leave(
        Request $request,
        Events $event,
        ParticipationRepository $participationRepository,
        EntityManagerInterface $em,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        if (!$this->isCsrfTokenValid('leave_event_' . $event->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        $existing = $participationRepository->findOneForUser($event, $user);
        if (!$existing) {
            $this->addFlash('info', "Tu ne participes pas à cet événement.");
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        $em->remove($existing);
        $em->flush();

        $this->addFlash('success', "Tu as quitté cet événement.");
        return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
    }


    // Crée un nouvel événement.
    // Les informations saisies dans le formulaire sont
    // enregistrées dans la base de données.
    // Cette méthode gère également l'image de couverture
    // ainsi que les photos de la galerie.
    #[Route('/events/new', name: 'app_events_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {

        $event = new Events();
        $event->setCreatedAt(new \DateTimeImmutable());
        $event->setRatingAverage('0');
        $event->setGallery(null);
        $user = $this->getUser();
        $event->setOrganisateur($this->getUser());

        $form = $this->createForm(EventsType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverPhoto = $form->get('coverPhoto')->getData();
            if ($coverPhoto) {
                $event->setCoverPhoto(file_get_contents($coverPhoto->getPathname()));
            }

            if (!$event->getRatingAverage()) {
                $event->setRatingAverage('0');
            }

            $galleryUploads = $form->get('galleryPhotos')->getData();
            if (is_array($galleryUploads) && count($galleryUploads) > 8) {
                $this->addFlash('error', 'Galerie: 8 photos maximum.');
                return $this->render('events/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            if (is_array($galleryUploads)) {
                foreach ($galleryUploads as $uploaded) {
                    if (!$uploaded) {
                        continue;
                    }
                    $photo = new EventPhoto();
                    $photo->setPhoto(file_get_contents($uploaded->getPathname()));
                    $event->addGalleryPhoto($photo);
                    $em->persist($photo);
                }
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
    // Modifie un événement existant.
    // Seul l'organisateur ou un gestionnaire d'événements
    // peut effectuer cette opération.
    // Les nouvelles informations sont ensuite sauvegardées.
    #[Route('/events/{id}/edit', name: 'app_events_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Events $event, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (
            !$this->isGranted('ROLE_EVENT_MANAGER') &&
            $event->getOrganisateur()?->getId() !== $user->getId()
        ) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        $form = $this->createForm(EventsType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverPhoto = $form->get('coverPhoto')->getData();
            if ($coverPhoto) {
                $event->setCoverPhoto(file_get_contents($coverPhoto->getPathname()));
            }

            if (!$event->getRatingAverage()) {
                $event->setRatingAverage('0');
            }

            $existingCount = $event->getGalleryPhotos()->count();
            $remaining = max(0, 8 - $existingCount);

            $galleryUploads = $form->get('galleryPhotos')->getData();
            if (is_array($galleryUploads) && count($galleryUploads) > $remaining) {
                $this->addFlash('error', sprintf('Galerie: %d photo(s) maximum supplémentaire(s).', $remaining));
                return $this->render('events/edit.html.twig', [
                    'event' => $event,
                    'form' => $form->createView(),
                ]);
            }
            if (is_array($galleryUploads)) {
                foreach ($galleryUploads as $uploaded) {
                    if (!$uploaded) {
                        continue;
                    }
                    $photo = new EventPhoto();
                    $photo->setPhoto(file_get_contents($uploaded->getPathname()));
                    $event->addGalleryPhoto($photo);
                    $em->persist($photo);
                }
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

    // Permet à un utilisateur de donner une note à un événement.
    // Si une note existe déjà, elle est mise à jour.
    // La moyenne des notes est ensuite recalculée.
    #[Route('/events/{id}/rate', name: 'app_events_rate', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function rate(Request $request, Events $event, EventRatingRepository $eventRatingRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        if (!$this->isCsrfTokenValid('rate_event_' . $event->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        $ratingRaw = $request->request->get('rating');
        $rating = is_string($ratingRaw) && ctype_digit($ratingRaw) ? (int) $ratingRaw : null;
        if ($rating === null || $rating < 1 || $rating > 5) {
            $this->addFlash('error', 'Note invalide (1 à 5).');
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        $existing = $eventRatingRepository->findOneForUser($event, $user);
        if (!$existing) {
            $existing = new EventRating();
            $existing->setEventID($event);
            $existing->setUserID($user);
        }
        $existing->setRating($rating);

        $em->persist($existing);
        $em->flush();

        $avg = $eventRatingRepository->getAverageForEvent($event);
        $event->setRatingAverage((string) round($avg, 1));
        $em->flush();

        $this->addFlash('success', 'Merci, ta note a été enregistrée.');
        return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
    }

    // Supprime une photo de la galerie d'un événement.
    // Cette action est réservée aux gestionnaires d'événements
    // après vérification des droits d'accès.
    #[Route('/events/{id}/photos/{photo}/delete', name: 'app_events_photo_delete', requirements: ['id' => '\\d+', 'photo' => '\\d+'], methods: ['POST'])]
    public function deletePhoto(Request $request, Events $event, EventPhoto $photo, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EVENT_MANAGER');

        if ($photo->getEventID()?->getId() !== $event->getId()) {
            throw $this->createNotFoundException('Photo introuvable.');
        }

        if (!$this->isCsrfTokenValid('delete_event_photo_' . $photo->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_events_edit', ['id' => $event->getId()]);
        }

        $em->remove($photo);
        $em->flush();

        $this->addFlash('success', 'Photo supprimée.');
        return $this->redirectToRoute('app_events_edit', ['id' => $event->getId()]);
    }

    // Supprime définitivement un événement via une route API.
    // Cette fonctionnalité est réservée aux gestionnaires
    // d'événements disposant des droits nécessaires.
    #[Route('/api/events/{id}', methods: ['DELETE'])]
    public function apiDelete(Events $event, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_EVENT_MANAGER');

        $em->remove($event);
        $em->flush();

        return $this->json([
            'message' => 'deleted'
        ]);
    }
}
