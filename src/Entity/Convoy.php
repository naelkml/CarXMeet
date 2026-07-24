<?php

/**
 * Entité Convoy - Représente un convoi organisé dans le cadre d'un événement.
 *
 * Un convoi est un groupe de voitures qui se retrouvent en un point de départ
 * pour rejoindre ensemble un événement. Cette entité stocke les informations
 * logistiques du convoi : lieu de départ, heure, date, et le nombre de participants.
 *
 * Un convoi est toujours rattaché à un événement (Events), et des participants
 * peuvent le rejoindre via l'entité ConvoyParticipation.
 *
 * Relations :
 * - Un convoi appartient à UN événement (ManyToOne vers Events)
 * - Un convoi peut avoir PLUSIEURS participations (OneToMany vers ConvoyParticipation)
 */

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ConvoyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\Convoy\CreateConvoyController;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;

/**
 * #[ORM\Entity] : cette classe est une entité Doctrine (table en base de données).
 *
 * #[ApiResource] expose les opérations HTTP disponibles :
 * - GetCollection : liste tous les convois (avec infos participants et utilisateurs inclus)
 * - Get : récupère un convoi par son ID
 * - Post : crée un nouveau convoi via un contrôleur personnalisé (CreateConvoyController)
 *   Le "deserialize: false" signifie que le contrôleur gère lui-même la transformation
 *   des données reçues (souvent pour gérer des fichiers ou une logique métier spéciale).
 *
 * #[ApiFilter] : permet de filtrer les convois par événement dans l'URL
 * ex : GET /api/convoys?eventID=5 → retourne uniquement les convois de l'événement 5
 */
#[ORM\Entity(repositoryClass: ConvoyRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['convoy:read', 'convoy_participation:read', 'user:read']]),
        new Get(normalizationContext: ['groups' => ['convoy:read', 'convoy_participation:read', 'user:read']]),
        new Post(
            controller: CreateConvoyController::class,
            deserialize: false,
            read: false,
            normalizationContext: ['groups' => ['convoy:read']],
        ),
    ],
    normalizationContext: ['groups' => ['convoy:read']],
    denormalizationContext: ['groups' => ['convoy:write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['eventID' => 'exact'])]
class Convoy
{
    /**
     * Identifiant unique du convoi, auto-généré par la base de données.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['convoy:read'])]
    private ?int $id = null;

    /**
     * L'événement auquel ce convoi est rattaché.
     *
     * #[ORM\ManyToOne] : plusieurs convois peuvent appartenir au même événement,
     * mais ce convoi n'appartient qu'à un seul événement.
     * #[ORM\JoinColumn(nullable: false)] : l'événement est OBLIGATOIRE,
     * un convoi sans événement associé est interdit en base de données.
     */
    #[ORM\ManyToOne(inversedBy: 'convoys')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['convoy:read', 'convoy:write'])]
    private ?Events $eventID = null;

    /**
     * Lieu de départ du convoi (ex : "Parking du Centre Commercial, Lyon").
     */
    #[ORM\Column(length: 255)]
    #[Groups(['convoy:read', 'convoy:write'])]
    private ?string $departureLocation = null;

    /**
     * Heure de départ du convoi (ex : "14:30").
     * Stockée comme une chaîne de caractères pour la flexibilité.
     */
    #[ORM\Column(length: 255)]
    #[Groups(['convoy:read', 'convoy:write'])]
    private ?string $departureTime = null;

    /**
     * Date de départ du convoi (ex : "2024-07-14").
     * nullable: true signifie que ce champ est optionnel (peut être null).
     * Limité à 10 caractères (format JJ/MM/AAAA ou AAAA-MM-JJ).
     */
    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['convoy:read', 'convoy:write'])]
    private ?string $departureDate = null;

    /**
     * Nombre ou description des participants au convoi.
     * Stocké comme texte (ex : "12 voitures inscrites").
     * nullable: true : champ optionnel.
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['convoy:read', 'convoy:write'])]
    private ?string $participants = null;

    /**
     * Liste des participations (inscriptions) à ce convoi.
     *
     * Collection<int, ConvoyParticipation> : c'est une liste d'objets ConvoyParticipation.
     * La syntaxe Collection est propre à Doctrine, similaire à un tableau PHP mais
     * avec des fonctionnalités supplémentaires (filtrage, itération, etc.).
     *
     * #[ORM\OneToMany] : UN convoi peut avoir PLUSIEURS participations.
     * "mappedBy: 'convoyID'" : dans ConvoyParticipation, c'est la propriété $convoyID
     * qui fait le lien vers ce convoi.
     * "orphanRemoval: true" : si on retire une participation de cette liste, elle sera
     * automatiquement supprimée de la base de données.
     * "cascade: ['persist']" : quand on sauvegarde un convoi, les participations associées
     * sont aussi sauvegardées automatiquement.
     */
    /**
     * @var Collection<int, ConvoyParticipation>
     */
    #[ORM\OneToMany(targetEntity: ConvoyParticipation::class, mappedBy: 'convoyID', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['convoy:read'])]
    private Collection $memberships;

    /**
     * Constructeur : méthode spéciale appelée automatiquement à la création d'un objet Convoy.
     *
     * On initialise $memberships avec un ArrayCollection vide.
     * ArrayCollection est la structure de Doctrine pour gérer des listes d'entités liées.
     * Sans cette initialisation, $memberships serait null et causerait des erreurs.
     */
    public function __construct()
    {
        $this->memberships = new ArrayCollection();
    }

    /**
     * Retourne l'identifiant unique du convoi.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne l'événement associé à ce convoi.
     */
    public function getEventID(): ?Events
    {
        return $this->eventID;
    }

    /**
     * Associe ce convoi à un événement.
     */
    public function setEventID(?Events $eventID): static
    {
        $this->eventID = $eventID;

        return $this;
    }

    /**
     * Retourne le lieu de départ du convoi.
     */
    public function getDepartureLocation(): ?string
    {
        return $this->departureLocation;
    }

    /**
     * Définit le lieu de départ du convoi.
     */
    public function setDepartureLocation(string $departureLocation): static
    {
        $this->departureLocation = $departureLocation;

        return $this;
    }

    /**
     * Retourne l'heure de départ du convoi.
     */
    public function getDepartureTime(): ?string
    {
        return $this->departureTime;
    }

    /**
     * Définit l'heure de départ du convoi.
     */
    public function setDepartureTime(string $departureTime): static
    {
        $this->departureTime = $departureTime;

        return $this;
    }

    /**
     * Retourne la date de départ du convoi (peut être null).
     */
    public function getDepartureDate(): ?string
    {
        return $this->departureDate;
    }

    /**
     * Définit la date de départ du convoi.
     * Accepte null pour effacer la date.
     */
    public function setDepartureDate(?string $departureDate): static
    {
        $this->departureDate = $departureDate;
        return $this;
    }

    /**
     * Retourne la description des participants au convoi.
     */
    public function getParticipants(): ?string
    {
        return $this->participants;
    }

    /**
     * Définit la description des participants.
     */
    public function setParticipants(?string $participants): static
    {
        $this->participants = $participants;

        return $this;
    }

    /**
     * Retourne la collection de toutes les participations à ce convoi.
     *
     * @return Collection<int, ConvoyParticipation>
     */
    public function getMemberships(): Collection
    {
        return $this->memberships;
    }

    /**
     * Ajoute une participation à ce convoi.
     *
     * On vérifie d'abord si la participation n'est pas déjà dans la liste
     * (avec "contains") pour éviter les doublons.
     * Ensuite on fait le lien dans les deux sens : on ajoute la participation
     * à la liste du convoi, ET on indique à la participation quel est son convoi.
     * C'est ce qu'on appelle la "synchronisation bidirectionnelle des relations".
     */
    public function addMembership(ConvoyParticipation $membership): static
    {
        if (!$this->memberships->contains($membership)) {
            $this->memberships->add($membership);
            $membership->setConvoyID($this);
        }

        return $this;
    }

    /**
     * Retire une participation de ce convoi.
     *
     * "removeElement" retire l'élément de la collection Doctrine.
     * Grâce à "orphanRemoval: true" sur la relation, la participation sera
     * automatiquement supprimée de la base de données.
     * On ne remet pas la FK à null car JoinColumn est non-nullable (obligatoire).
     */
    public function removeMembership(ConvoyParticipation $membership): static
    {
        if ($this->memberships->removeElement($membership)) {
            // orphanRemoval handles the delete; avoid setting FK to null (JoinColumn is non-nullable).
        }

        return $this;
    }
}
