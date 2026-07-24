<?php

/**
 * Fichier : DeleteEventPhotoController.php
 *
 * Ce fichier contient le contrôleur chargé de SUPPRIMER une photo de la galerie d'un événement.
 *
 * Lorsqu'un gestionnaire d'événements souhaite retirer une photo de la galerie d'un de ses
 * événements, il envoie une requête HTTP DELETE sur l'URL de cette photo spécifique.
 *
 * API Platform résout automatiquement l'objet EventPhoto à partir de l'identifiant dans l'URL.
 * Ce contrôleur vérifie que l'utilisateur a bien le rôle requis, puis supprime la photo.
 *
 * Note : contrairement à DeleteEventController, ce contrôleur ne vérifie pas si l'utilisateur
 * est le propriétaire de la photo — il vérifie uniquement le rôle ROLE_EVENT_MANAGER.
 */

namespace App\Controller\Api\Event;

use App\Entity\EventPhoto;
use App\Service\Api\ApiJsonResponder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * #[AsController] : marque cette classe comme contrôleur d'API pour Symfony.
 */
#[AsController]
final class DeleteEventPhotoController extends AbstractController
{
    /**
     * Constructeur : injection automatique des dépendances par Symfony.
     *
     * @param EntityManagerInterface $em       Le gestionnaire de base de données Doctrine.
     * @param ApiJsonResponder        $responder Service de réponses JSON.
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ApiJsonResponder $responder,
    ) {
    }

    /**
     * Point d'entrée du contrôleur.
     *
     * API Platform injecte automatiquement l'objet EventPhoto correspondant à l'identifiant
     * présent dans l'URL de la requête DELETE.
     *
     * Étapes du traitement :
     * 1. Vérifier que l'utilisateur a le rôle ROLE_EVENT_MANAGER.
     * 2. Supprimer la photo de la base de données.
     * 3. Retourner une réponse vide avec le code HTTP 204 (No Content) — standard REST
     *    pour indiquer qu'une suppression a réussi sans contenu à retourner.
     *
     * @param EventPhoto $photo La photo à supprimer, résolue automatiquement par API Platform.
     * @return Response         Une réponse vide avec le code HTTP 204.
     */
    public function __invoke(EventPhoto $photo): Response
    {
        // Seul un gestionnaire d'événements peut supprimer une photo.
        $this->denyAccessUnlessGranted('ROLE_EVENT_MANAGER');

        // Marque la photo pour suppression dans Doctrine.
        $this->em->remove($photo);
        // Exécute la requête SQL DELETE.
        $this->em->flush();

        // Retourne une réponse vide : HTTP 204 No Content (succès sans corps de réponse).
        // C'est la convention REST pour les suppressions réussies.
        return $this->responder->empty();
    }
}
