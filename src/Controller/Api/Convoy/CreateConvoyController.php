<?php

/**
 * Fichier : CreateConvoyController.php
 *
 * Ce fichier contient le contrôleur chargé de CRÉER un convoi (convoy) via l'API.
 *
 * Un "convoi" dans cette application représente un groupe de voitures qui se retrouvent
 * à un point de départ commun pour rejoindre ensemble un événement.
 *
 * Ce contrôleur est appelé lorsqu'un utilisateur envoie une requête HTTP POST à l'API
 * pour créer un nouveau convoi. Il vérifie que l'utilisateur est connecté, que les données
 * envoyées sont valides, puis enregistre le convoi en base de données.
 *
 * Notions clés pour un débutant :
 * - Un "contrôleur" est une classe PHP qui reçoit une requête (ex. : un clic sur un bouton)
 *   et renvoie une réponse (ex. : les données du convoi créé en JSON).
 * - "API" signifie que ce contrôleur ne renvoie pas une page HTML, mais des données brutes
 *   au format JSON (lisibles par une application mobile ou un front-end JavaScript).
 * - Doctrine est la bibliothèque utilisée pour lire/écrire dans la base de données.
 */

namespace App\Controller\Api\Convoy;

use App\Entity\Convoy;
use App\Entity\Events;
use App\Entity\User;
use App\Service\Api\ApiJsonResponder;
use App\Service\Api\FormDataHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Attribut #[AsController] : indique à Symfony que cette classe est un contrôleur d'API.
 * Le mot-clé "final" signifie que cette classe ne peut pas être héritée (étendue) par une autre.
 * "extends AbstractController" donne accès à des méthodes utilitaires comme getUser() ou denyAccessUnlessGranted().
 */
#[AsController]
final class CreateConvoyController extends AbstractController
{
    /**
     * Constructeur : Symfony injecte automatiquement les dépendances dont ce contrôleur a besoin.
     *
     * @param EntityManagerInterface $em       Le gestionnaire d'entités Doctrine : permet de lire
     *                                          et d'écrire dans la base de données.
     * @param ApiJsonResponder        $responder Service utilitaire qui formate les réponses JSON de l'API.
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ApiJsonResponder $responder,
    ) {
    }

    /**
     * Méthode principale du contrôleur, appelée automatiquement par Symfony.
     *
     * La méthode magique __invoke() est déclenchée lorsqu'on appelle l'objet comme une fonction.
     * Symfony l'utilise pour les contrôleurs "invocables" (un seul point d'entrée par classe).
     *
     * Étapes du traitement :
     * 1. Vérifier que l'utilisateur est connecté (ROLE_USER).
     * 2. Vérifier que l'utilisateur est bien une instance de notre entité User.
     * 3. Décoder les données JSON envoyées dans la requête.
     * 4. Valider et récupérer l'identifiant de l'événement associé.
     * 5. Valider le lieu de départ.
     * 6. Créer et sauvegarder le convoi en base de données.
     * 7. Retourner le convoi créé au format JSON avec le code HTTP 201 (Created).
     *
     * @param Request $request La requête HTTP entrante (contient les données envoyées par le client).
     * @return Response        La réponse HTTP (ici : les données du convoi créé en JSON).
     */
    public function __invoke(Request $request): Response
    {
        // Vérifie que l'utilisateur est authentifié et possède le rôle ROLE_USER.
        // Si ce n'est pas le cas, Symfony lance automatiquement une exception 403 (Accès refusé).
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Vérifie que l'objet utilisateur retourné est bien une instance de notre classe User.
        // getUser() peut retourner null ou un autre type si le système de sécurité n'est pas bien configuré.
        if (!$this->getUser() instanceof User) {
            throw new AccessDeniedHttpException();
        }

        // Décode le corps de la requête HTTP (le JSON envoyé par le client) en tableau PHP associatif.
        $payload = $this->decodePayload($request);

        // Récupère l'identifiant de l'événement depuis le payload.
        // L'opérateur ?? (null coalescing) retourne null si la clé n'existe pas dans le tableau.
        $eventRaw = $payload['eventID'] ?? null;
        if ($eventRaw === null) {
            // Si l'événement n'est pas fourni, on lève une exception 400 Bad Request.
            throw new BadRequestHttpException('L\'événement est obligatoire.');
        }

        // L'eventID peut arriver sous deux formes : un entier (ex: 42) ou une chaîne IRI (ex: "/api/events/42").
        // On normalise ça pour obtenir un simple entier.
        if (is_int($eventRaw)) {
            // Cas simple : c'est déjà un entier.
            $eventId = $eventRaw;
        } elseif (is_string($eventRaw)) {
            // Cas IRI : on extrait le nombre de la fin de la chaîne (ex: "/api/events/42" → 42).
            $eventId = FormDataHelper::resolveIriId(trim($eventRaw));
        } else {
            // Aucun format reconnu.
            $eventId = null;
        }

        if (!$eventId) {
            throw new BadRequestHttpException('L\'événement est obligatoire.');
        }

        // Cherche l'événement dans la base de données grâce à son identifiant.
        // find() retourne null si aucun enregistrement ne correspond.
        $event = $this->em->getRepository(Events::class)->find($eventId);
        if (!$event) {
            // Si l'événement n'existe pas, on retourne une erreur 404 Not Found.
            throw new NotFoundHttpException('Événement introuvable.');
        }

        // Récupère et nettoie le lieu de départ (trim() supprime les espaces en début/fin de chaîne).
        $departureLocation = is_string($payload['departureLocation'] ?? null)
            ? trim($payload['departureLocation'])
            : '';
        if ($departureLocation === '') {
            throw new BadRequestHttpException('Le lieu de départ est obligatoire.');
        }

        // Crée un nouvel objet Convoy (entité Doctrine qui correspond à une ligne dans la table convoy).
        $convoy = new Convoy();
        // Associe l'événement trouvé à ce convoi.
        $convoy->setEventID($event);
        // Définit le lieu de départ.
        $convoy->setDepartureLocation($departureLocation);
        // Définit la date de départ si elle est fournie, sinon null.
        $convoy->setDepartureDate(
            is_string($payload['departureDate'] ?? null) && $payload['departureDate'] !== ''
                ? $payload['departureDate']
                : null
        );
        // Définit l'heure de départ si elle est fournie, sinon '00:00' par défaut.
        $convoy->setDepartureTime(
            is_string($payload['departureTime'] ?? null) && $payload['departureTime'] !== ''
                ? $payload['departureTime']
                : '00:00'
        );

        // persist() : dit à Doctrine de "surveiller" cet objet (l'ajouter à la file d'attente).
        $this->em->persist($convoy);
        // flush() : exécute réellement la requête SQL INSERT en base de données.
        $this->em->flush();

        // Retourne le convoi créé sous forme de JSON, avec le code HTTP 201 (Created).
        // Le tableau ['convoy:read'] indique quels champs de l'entité doivent être inclus dans la réponse
        // (contrôle via les groupes de sérialisation Symfony).
        return $this->responder->item($convoy, Response::HTTP_CREATED, ['convoy:read']);
    }

    /**
     * Décode le corps brut de la requête HTTP en tableau PHP.
     *
     * Cette méthode gère deux cas :
     * 1. Si la requête contient du JSON brut dans son corps (Content-Type: application/json),
     *    on le décode en tableau associatif PHP.
     * 2. Si le corps est vide, on retourne les données du formulaire (request->request->all()).
     *
     * @param Request $request La requête HTTP.
     * @return array<string, mixed> Un tableau associatif des données envoyées par le client.
     */
    private function decodePayload(Request $request): array
    {
        // Récupère le corps brut de la requête (le texte JSON envoyé par le client).
        $content = $request->getContent();
        if ($content === '') {
            // Si le corps est vide, on essaie de lire les données d'un formulaire classique.
            return $request->request->all();
        }

        // JsonDecode::ASSOCIATIVE => true : retourne un tableau PHP au lieu d'un objet stdClass.
        $decoded = (new JsonDecode([JsonDecode::ASSOCIATIVE => true]))->decode($content, JsonEncoder::FORMAT);
        // Si le décodage réussit et retourne un tableau, on le retourne ; sinon tableau vide.
        return is_array($decoded) ? $decoded : [];
    }
}
