<?php

/**
 * Entité EventPhoto - Représente une photo de galerie associée à un événement.
 *
 * Chaque événement peut avoir une galerie de photos prises pendant l'événement.
 * Cette entité stocke chaque photo individuellement avec :
 * - la photo elle-même (en binaire dans la base de données)
 * - l'événement auquel elle appartient
 * - la date d'ajout
 *
 * Concept de stockage "blob" :
 * Contrairement à d'autres entités qui stockent uniquement le chemin d'un fichier,
 * ici la photo est stockée directement en base de données sous forme binaire (BLOB).
 * Un BLOB (Binary Large OBject) est un type de données SQL pour stocker des fichiers
 * binaires (images, vidéos, PDF...) directement dans la base de données.
 *
 * La méthode getImageBase64() convertit ensuite ce blob en chaîne Base64,
 * un format texte que les navigateurs peuvent afficher directement dans une balise <img>.
 */

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\EventPhotoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Delete;
use App\Controller\Api\Event\DeleteEventPhotoController;

/**
 * #[ORM\Entity] : entité Doctrine.
 *
 * L'API expose :
 * - GetCollection : liste toutes les photos d'événements
 * - Get : récupère une photo spécifique
 * - Delete : supprime une photo via un contrôleur personnalisé
 *   (DeleteEventPhotoController gère probablement la suppression du fichier + de l'entrée BDD)
 */
#[ORM\Entity(repositoryClass: EventPhotoRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['event_photo:read']]),
        new Get(normalizationContext: ['groups' => ['event_photo:read']]),
        new Delete(
            controller: DeleteEventPhotoController::class,
        ),
    ],
    normalizationContext: ['groups' => ['event_photo:read']],
    denormalizationContext: ['groups' => ['event_photo:write']]
)]
class EventPhoto
{
    /**
     * Identifiant unique de la photo, auto-généré.
     * Ce champ apparaît dans les réponses de l'API pour cette entité
     * ET dans les réponses de l'entité Events (quand on inclut la galerie).
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['event_photo:read', 'event:read'])]
    private ?int $id = null;

    /**
     * L'événement auquel cette photo appartient.
     *
     * #[ORM\ManyToOne] : plusieurs photos peuvent appartenir au même événement.
     * "inversedBy: 'galleryPhotos'" : dans Events, la propriété $galleryPhotos
     * liste toutes les photos de galerie de cet événement.
     * nullable: false : une photo doit être rattachée à un événement.
     */
    #[ORM\ManyToOne(inversedBy: 'galleryPhotos')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['event_photo:read', 'event_photo:write'])]
    private ?Events $eventID = null;

    /**
     * Données binaires de la photo stockée en base de données.
     *
     * type: 'blob' indique à Doctrine que ce champ contient des données binaires.
     * En PHP, ce champ peut être une ressource (stream PHP) ou une chaîne binaire.
     * Il n'a pas de type déclaré (pas de ?string) car les blobs sont gérés différemment.
     * Ce champ n'a pas d'annotation #[Groups] : il n'est JAMAIS envoyé directement
     * dans l'API (les données binaires brutes ne sont pas sérialisables en JSON).
     * À la place, on utilise getImageBase64() pour convertir en format lisible.
     */
    #[ORM\Column(type: 'blob')]
    private $photo;

    /**
     * Date et heure d'ajout de la photo à la galerie.
     * Initialisée automatiquement dans le constructeur.
     */
    #[ORM\Column]
    #[Groups(['event_photo:read', 'event:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Constructeur : initialise automatiquement la date d'ajout à maintenant.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * Retourne l'identifiant unique de la photo.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne l'événement associé à cette photo.
     */
    public function getEventID(): ?Events
    {
        return $this->eventID;
    }

    /**
     * Associe cette photo à un événement.
     */
    public function setEventID(?Events $eventID): static
    {
        $this->eventID = $eventID;
        return $this;
    }

    /**
     * Retourne les données binaires brutes de la photo.
     * Peut retourner une ressource PHP (stream) ou une chaîne binaire.
     * Ne pas utiliser directement pour l'affichage : préférer getImageBase64().
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Définit les données binaires de la photo.
     */
    public function setPhoto($photo): static
    {
        $this->photo = $photo;
        return $this;
    }

    /**
     * Retourne la date d'ajout de la photo.
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Convertit la photo binaire en chaîne Base64 pour l'affichage dans l'API.
     *
     * #[Groups] : cette méthode (et non une propriété) sera incluse dans la réponse JSON
     * de l'API. API Platform peut sérialiser les méthodes "get..." comme des propriétés.
     *
     * Fonctionnement :
     * 1. Si aucune photo n'est stockée, on retourne null.
     * 2. Les données peuvent être un "stream" (ressource PHP ouverte) ou des données brutes.
     *    - is_resource() vérifie si c'est un flux ouvert.
     *    - stream_get_meta_data() récupère les infos du flux.
     *    - Si le flux est "seekable" (on peut se déplacer dedans), on le remet au début (rewind).
     *    - stream_get_contents() lit tout le contenu du flux.
     * 3. base64_encode() transforme les données binaires en une chaîne de texte lisible.
     *    Le résultat peut être utilisé directement dans une balise HTML : <img src="data:image/jpeg;base64,XXX">
     */
    #[Groups(['event_photo:read', 'event:read'])]
    public function getImageBase64(): ?string
    {
        if (!$this->photo) {
            return null;
        }

        if (is_resource($this->photo)) {
            $meta = stream_get_meta_data($this->photo);
            if (!empty($meta['seekable'])) {
                rewind($this->photo);
            }
            $data = stream_get_contents($this->photo);
        } else {
            $data = $this->photo;
        }

        return base64_encode($data);
    }
}
