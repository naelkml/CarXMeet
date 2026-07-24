<?php

/**
 * Fichier : UpdateEventController.php
 *
 * Ce fichier contient le contrôleur chargé de METTRE À JOUR un événement existant via l'API.
 *
 * Il est déclenché lorsqu'un gestionnaire d'événements envoie une requête HTTP PATCH ou PUT
 * sur l'URL d'un événement (ex : PATCH /api/events/42).
 *
 * Principe de la mise à jour partielle :
 * On ne modifie que les champs présents dans la requête. Si un champ n'est pas envoyé,
 * sa valeur en base de données reste inchangée. C'est le comportement attendu pour un PATCH.
 *
 * Ce contrôleur gère aussi l'ajout de nouvelles photos (couverture et galerie),
 * dans la limite du nombre maximum de photos déjà existantes.
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
 * #[AsController] : marque cette classe comme contrôleur d'API.
 * "final" : cette classe ne peut pas être étendue.
 */
#[AsController]
final class UpdateEventController extends AbstractController
{
    /**
     * Constructeur : injection automatique des dépendances.
     *
     * @param EntityManagerInterface $em       Gestionnaire de base de données Doctrine.
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
     * API Platform injecte automatiquement l'objet Events à partir de l'identifiant dans l'URL.
     *
     * Étapes du traitement :
     * 1. Vérifier que l'utilisateur a le rôle ROLE_EVENT_MANAGER.
     * 2. Mettre à jour uniquement les champs présents dans la requête (mise à jour partielle).
     * 3. Gérer la modification de la région associée.
     * 4. Gérer le remplacement de la photo de couverture.
     * 5. Gérer l'ajout de nouvelles photos de galerie (en respectant le maximum de 8 au total).
     * 6. Sauvegarder les modifications en base de données.
     * 7. Retourner l'événement mis à jour au format JSON.
     *
     * @param Events  $event   L'événement à modifier, résolu automatiquement par API Platform.
     * @param Request $request La requête HTTP contenant les données de mise à jour.
     * @return Response         La réponse JSON contenant l'événement mis à jour.
     */
    public function __invoke(Events $event, Request $request): Response
    {
        // Seul un gestionnaire d'événements peut modifier un événement.
        $this->denyAccessUnlessGranted('ROLE_EVENT_MANAGER');

        // Mise à jour partielle des champs textuels.
        // Pour chaque champ, on vérifie d'abord s'il est présent dans la requête avant de le modifier.

        // Si un titre est fourni, on le met à jour.
        // L'affectation dans le if() est une écriture compacte : si getString() retourne une valeur
        // non-nulle, elle est stockée dans $title ET la condition est vraie.
        if ($title = FormDataHelper::getString($request, 'title')) {
            $event->setTitle($title);
        }

        // Pour les champs optionnels, on utilise request->has() pour savoir si le champ
        // a été envoyé, même s'il est vide (on autorise une mise à jour vers une valeur vide).
        if ($request->request->has('description')) {
            $event->setDescription(FormDataHelper::getString($request, 'description') ?? '');
        }
        if ($request->request->has('type')) {
            $event->setType(FormDataHelper::getString($request, 'type') ?? '');
        }
        if ($request->request->has('Date')) {
            $event->setDate(FormDataHelper::getString($request, 'Date') ?? '');
        }
        if ($request->request->has('location')) {
            $event->setLocation(FormDataHelper::getString($request, 'location') ?? '');
        }
        if ($request->request->has('organisateur')) {
            $event->setOrganisateur(FormDataHelper::getString($request, 'organisateur') ?? '');
        }

        // Gestion de la mise à jour de la région.
        if ($request->request->has('regionID')) {
            $regionRaw = FormDataHelper::getString($request, 'regionID');
            if (!$regionRaw) {
                // Si la valeur envoyée est vide/null, on dissocie la région de l'événement.
                $event->setRegionID(null);
            } else {
                // Résout l'IRI ou l'entier pour obtenir l'ID numérique de la région.
                $regionId = FormDataHelper::resolveIriId($regionRaw);
                // Cherche la région en base. find() retourne null si non trouvée.
                $region = $regionId ? $this->em->getRepository(Region::class)->find($regionId) : null;
                if (!$region) {
                    throw new NotFoundHttpException('Région introuvable.');
                }
                $event->setRegionID($region);
            }
        }

        // Gestion du remplacement de la photo de couverture.
        $cover = FormDataHelper::getUploadedFiles($request, 'coverPhoto')[0] ?? null;
        if ($cover) {
            // Remplace le contenu binaire de la photo de couverture existante.
            $event->setCoverPhoto(file_get_contents($cover->getPathname()));
        }

        // Gestion de l'ajout de nouvelles photos de galerie.
        // On calcule combien d'emplacements il reste (maximum 8 photos au total).
        $existingCount = $event->getGalleryPhotos()->count();
        // max(0, ...) garantit qu'on ne peut pas avoir un nombre négatif de places restantes.
        $remaining = max(0, 8 - $existingCount);

        // Récupère les nouveaux fichiers uploadés.
        $uploads = FormDataHelper::getUploadedFiles($request, 'galleryPhotos');
        // Vérifie qu'on ne dépasse pas le nombre de places disponibles.
        if (count($uploads) > $remaining) {
            throw new BadRequestHttpException(sprintf('Galerie: %d photo(s) maximum supplémentaire(s).', $remaining));
        }

        // Ajoute chaque nouvelle photo à la galerie de l'événement.
        foreach ($uploads as $uploaded) {
            $photo = new EventPhoto();
            $photo->setPhoto(file_get_contents($uploaded->getPathname()));
            $event->addGalleryPhoto($photo);
            $this->em->persist($photo);
        }

        // Sécurité : si la note moyenne n'a jamais été initialisée, on la met à '0'.
        if (!$event->getRatingAverage()) {
            $event->setRatingAverage('0');
        }

        // flush() : enregistre toutes les modifications en base de données (UPDATE SQL).
        // Pas besoin de persist() ici car l'objet $event est déjà "géré" par Doctrine
        // (il a été chargé depuis la base de données par API Platform).
        $this->em->flush();

        // Retourne l'événement mis à jour au format JSON avec le code 200 OK.
        return $this->responder->item($event, Response::HTTP_OK, ['event:read']);
    }
}
