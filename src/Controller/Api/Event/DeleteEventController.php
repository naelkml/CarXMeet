<?php

/**
 * Fichier : DeleteEventController.php
 *
 * Ce fichier contient le contrôleur chargé de SUPPRIMER un événement via l'API.
 *
 * Ce contrôleur est déclenché lorsqu'un utilisateur envoie une requête HTTP DELETE
 * sur l'URL d'un événement précis (ex : DELETE /api/events/42).
 *
 * Deux conditions sont nécessaires pour pouvoir supprimer un événement :
 * 1. L'utilisateur doit avoir le rôle ROLE_EVENT_MANAGER.
 * 2. L'utilisateur doit être l'organisateur de l'événement (on ne peut pas supprimer
 *    l'événement de quelqu'un d'autre).
 */

namespace App\Controller\Api\Event;

use App\Entity\Events;
use App\Service\Api\ApiJsonResponder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * #[AsController] : indique à Symfony/API Platform que c'est un contrôleur d'API.
 * "final" : cette classe ne peut pas être étendue par héritage.
 */
#[AsController]
final class DeleteEventController extends AbstractController
{
    /**
     * Constructeur : Symfony injecte automatiquement les dépendances.
     *
     * @param EntityManagerInterface $em       Gestionnaire Doctrine pour interagir avec la base de données.
     * @param ApiJsonResponder        $responder Service pour formater les réponses JSON de l'API.
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ApiJsonResponder $responder,
    ) {
    }

    /**
     * Point d'entrée du contrôleur, appelé automatiquement par Symfony.
     *
     * API Platform résout automatiquement l'objet Events à partir de l'identifiant
     * présent dans l'URL (ex : /api/events/42 → $event = l'objet Events avec l'id 42).
     * Si aucun événement ne correspond, API Platform renvoie une erreur 404 automatiquement.
     *
     * Étapes du traitement :
     * 1. Vérifier que l'utilisateur a le rôle ROLE_EVENT_MANAGER.
     * 2. Vérifier que l'utilisateur connecté est bien l'organisateur de cet événement.
     * 3. Supprimer l'événement de la base de données.
     * 4. Retourner une confirmation JSON de suppression.
     *
     * @param Events $event L'événement à supprimer, résolu automatiquement par API Platform via l'URL.
     * @return JsonResponse La réponse JSON confirmant la suppression.
     */
    public function __invoke(Events $event): JsonResponse
    {
        // Vérifie que l'utilisateur est un gestionnaire d'événements.
        // Sans ce rôle, une erreur 403 Forbidden est retournée automatiquement.
        $this->denyAccessUnlessGranted('ROLE_EVENT_MANAGER');

        // Vérifie que l'organisateur de l'événement est bien l'utilisateur connecté.
        // getOrganisateur() retourne l'objet User qui a créé l'événement.
        // getUser() retourne l'utilisateur actuellement connecté.
        // On compare les deux avec !== (différent strict : différent d'objet en mémoire).
        if ($event->getOrganisateur() !== $this->getUser()) {
            // Si l'utilisateur n'est pas l'organisateur, on refuse l'accès.
            // createAccessDeniedException() génère une exception 403 Forbidden.
            throw $this->createAccessDeniedException();
        }

        // Demande à Doctrine de marquer cet objet pour suppression.
        $this->em->remove($event);
        // Exécute la requête SQL DELETE en base de données.
        $this->em->flush();

        // Retourne une réponse JSON de succès avec un message de confirmation.
        return $this->responder->success([
            'message' => 'Event supprimé'
        ]);
    }
}
