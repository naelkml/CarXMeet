<?php

/**
 * Fichier : CreateEventController.php
 *
 * Ce fichier contient le contrôleur chargé de CRÉER un événement automobile via l'API.
 *
 * Un "événement" (Events) dans l'application représente un rassemblement de voitures
 * (Run, JDM, Drift, Stance…) organisé à une date et un lieu précis.
 *
 * Ce contrôleur est déclenché lorsqu'un utilisateur ayant le rôle "gestionnaire d'événements"
 * (ROLE_EVENT_MANAGER) envoie une requête HTTP POST avec les informations du nouvel événement.
 *
 * Il accepte à la fois des données textuelles (titre, description, date…) et des fichiers images
 * (photo de couverture, galerie de photos).
 */

namespace App\Controller\Api\Event;

use App\Entity\EventPhoto;
use App\Entity\Events;
use App\Entity\Region;
use App\Service\Api\ApiJsonResponder;
use App\Service\Api\FormDataHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * #[AsController] : marque cette classe comme contrôleur pour API Platform / Symfony.
 * "final" : cette classe ne peut pas être étendue (héritée).
 */
#[AsController]
final class CreateEventController extends AbstractController
{
    /**
     * Constructeur : Symfony injecte automatiquement les services nécessaires.
     *
     * @param EntityManagerInterface $em       Gestionnaire Doctrine pour interagir avec la base de données.
     * @param ApiJsonResponder        $responder Service pour formater et renvoyer des réponses JSON.
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ApiJsonResponder $responder,
    ) {
    }

    /**
     * Point d'entrée principal du contrôleur, appelé par Symfony à chaque requête.
     *
     * Étapes du traitement :
     * 1. Vérifier que l'utilisateur a le rôle ROLE_EVENT_MANAGER.
     * 2. Récupérer et valider les champs textuels (titre obligatoire, description, type…).
     * 3. Associer une région si un identifiant de région est fourni.
     * 4. Gérer l'upload de la photo de couverture.
     * 5. Gérer l'upload des photos de galerie (max 8).
     * 6. Sauvegarder l'événement en base de données.
     * 7. Retourner l'événement créé au format JSON (code 201 Created).
     *
     * @param Request $request La requête HTTP entrante avec les données multipart/form-data.
     * @return Response        La réponse JSON contenant l'événement créé.
     */
    public function __invoke(Request $request): Response
    {
        // Seul un utilisateur ayant le rôle ROLE_EVENT_MANAGER peut créer un événement.
        // Si l'utilisateur ne l'a pas, Symfony renvoie automatiquement une erreur 403 Forbidden.
        $this->denyAccessUnlessGranted('ROLE_EVENT_MANAGER');

        // Récupère le titre depuis les données du formulaire, en supprimant les espaces superflus.
        // FormDataHelper::getString() retourne null si le champ est absent ou vide.
        $title = FormDataHelper::getString($request, 'title');
        if (!$title) {
            // Le titre est obligatoire : on refuse la requête avec un code 400 Bad Request.
            throw new BadRequestHttpException('Le titre est obligatoire.');
        }

        // Crée un nouvel objet Events (entité Doctrine = une ligne dans la table events).
        $event = new Events();
        $event->setTitle($title);
        // ?? '' : si getString retourne null, on stocke une chaîne vide plutôt que null.
        $event->setDescription(FormDataHelper::getString($request, 'description') ?? '');
        $event->setType(FormDataHelper::getString($request, 'type') ?? '');
        $event->setDate(FormDataHelper::getString($request, 'Date') ?? '');
        $event->setLocation(FormDataHelper::getString($request, 'location') ?? '');
        // getUser() retourne l'utilisateur actuellement connecté. Il sera l'organisateur de l'événement.
        $event->setOrganisateur($this->getUser());
        // DateTimeImmutable : objet PHP représentant la date/heure actuelle, immuable (non modifiable).
        $event->setCreatedAt(new \DateTimeImmutable());
        // Note de moyenne initialisée à '0' (pas encore de notes données par les utilisateurs).
        $event->setRatingAverage('0');
        // La galerie principale est null pour l'instant (sera gérée via les photos individuelles).
        $event->setGallery(null);

        // Gestion de la région associée à l'événement.
        // resolveIriId() extrait un entier depuis une chaîne IRI (ex: "/api/regions/3" → 3) ou un chiffre direct.
        $regionId = FormDataHelper::resolveIriId(FormDataHelper::getString($request, 'regionID'));
        if ($regionId) {
            // Cherche la région correspondante en base de données.
            $region = $this->em->getRepository(Region::class)->find($regionId);
            if (!$region) {
                // Si la région n'existe pas, erreur 404 Not Found.
                throw new NotFoundHttpException('Région introuvable.');
            }
            $event->setRegionID($region);
        }

        // Récupère la photo de couverture si elle a été envoyée dans la requête.
        // getUploadedFiles() retourne un tableau de fichiers ; [0] prend le premier (ou null si vide).
        $cover = FormDataHelper::getUploadedFiles($request, 'coverPhoto')[0] ?? null;
        if ($cover) {
            // file_get_contents() lit le contenu binaire du fichier temporaire uploadé.
            // On stocke les données brutes de l'image directement dans la base de données (en BLOB).
            $event->setCoverPhoto(file_get_contents($cover->getPathname()));
        }

        // Attache les photos de galerie à l'événement (maximum 8 photos autorisées).
        $this->attachGalleryPhotos($event, $request, 8);

        // Indique à Doctrine de suivre cet objet pour insertion en base.
        $this->em->persist($event);
        // Exécute la requête SQL INSERT (et les INSERTs des photos de galerie).
        $this->em->flush();

        // Retourne la réponse JSON avec le code 201 Created.
        // ['event:read'] : groupe de sérialisation qui définit quels champs inclure dans la réponse.
        return $this->responder->item($event, Response::HTTP_CREATED, ['event:read']);
    }

    /**
     * Méthode privée : attache des photos de galerie à un événement.
     *
     * Cette méthode lit les fichiers uploadés depuis la requête, vérifie qu'on ne dépasse
     * pas le nombre maximum autorisé, puis crée un objet EventPhoto par image et l'associe
     * à l'événement.
     *
     * @param Events  $event   L'événement auquel on rattache les photos.
     * @param Request $request La requête HTTP contenant les fichiers uploadés.
     * @param int     $max     Le nombre maximum de photos autorisées dans la galerie.
     */
    private function attachGalleryPhotos(Events $event, Request $request, int $max): void
    {
        // Récupère tous les fichiers uploadés sous la clé 'galleryPhotos'.
        $uploads = FormDataHelper::getUploadedFiles($request, 'galleryPhotos');

        // Vérifie qu'on ne dépasse pas le maximum autorisé.
        // sprintf() permet d'injecter une valeur dans une chaîne (ici %d = nombre entier).
        if (count($uploads) > $max) {
            throw new BadRequestHttpException(sprintf('Galerie: %d photos maximum.', $max));
        }

        // Pour chaque fichier uploadé, on crée une entité EventPhoto et on la persiste.
        foreach ($uploads as $uploaded) {
            $photo = new EventPhoto();
            // Lit le contenu binaire du fichier et le stocke dans l'entité.
            $photo->setPhoto(file_get_contents($uploaded->getPathname()));
            // Ajoute la photo à la collection de photos de l'événement.
            $event->addGalleryPhoto($photo);
            // Demande à Doctrine de persister cette photo individuellement.
            $this->em->persist($photo);
        }
    }
}
