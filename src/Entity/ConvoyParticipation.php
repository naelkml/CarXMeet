<?php

/**
 * Entité ConvoyParticipation - Représente l'inscription d'un utilisateur à un convoi.
 *
 * Cette entité est une "table de jonction" enrichie : elle fait le lien entre
 * un convoi (Convoy) et un utilisateur (User), et ajoute des informations
 * supplémentaires comme la date d'inscription.
 *
 * Concept : quand un utilisateur clique sur "rejoindre ce convoi", une nouvelle
 * ligne ConvoyParticipation est créée en base de données avec :
 * - l'ID du convoi
 * - l'ID de l'utilisateur
 * - la date à laquelle il a rejoint
 *
 * La contrainte unique (#[ORM\UniqueConstraint]) interdit à un même utilisateur
 * de s'inscrire deux fois au même convoi.
 */

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ConvoyParticipationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;

/**
 * #[ORM\Table(name: 'convoy_participation')] : le nom de la table en BDD est explicitement
 * défini comme "convoy_participation" (par défaut Doctrine utilise le nom de la classe).
 *
 * #[ORM\UniqueConstraint] : empêche en base de données qu'un utilisateur s'inscrive
 * deux fois au même convoi. C'est une contrainte d'intégrité au niveau SQL.
 *
 * L'API expose :
 * - GetCollection : liste toutes les participations aux convois
 * - Get : récupère une participation spécifique
 * - Post : crée une nouvelle participation (inscription)
 * - Delete : supprime une participation (désinscription)
 *
 * #[ApiFilter] : filtre par convoyID ou userID dans l'URL
 * ex : GET /api/convoy_participations?convoyID=3 → toutes les inscriptions au convoi 3
 */
#[ORM\Entity(repositoryClass: ConvoyParticipationRepository::class)]
#[ORM\Table(name: 'convoy_participation')]
#[ORM\UniqueConstraint(name: 'uniq_convoy_user', columns: ['convoy_id_id', 'user_id_id'])]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['convoy_participation:read', 'user:read']]),
        new Get(normalizationContext: ['groups' => ['convoy_participation:read', 'user:read']]),
        new Post(denormalizationContext: ['groups' => ['convoy_participation:write']]),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['convoy_participation:read']],
    denormalizationContext: ['groups' => ['convoy_participation:write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['convoyID' => 'exact', 'userID' => 'exact'])]
class ConvoyParticipation
{
    /**
     * Identifiant unique de la participation, auto-généré.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['convoy_participation:read'])]
    private ?int $id = null;

    /**
     * Le convoi auquel l'utilisateur participe.
     *
     * #[ORM\ManyToOne] : plusieurs participations peuvent pointer vers le même convoi.
     * "inversedBy: 'memberships'" : dans la classe Convoy, la propriété $memberships
     * liste toutes les participations de ce convoi.
     * nullable: false : on ne peut pas créer une participation sans convoi associé.
     *
     * Note : ce champ n'est pas en lecture ('read') pour éviter les boucles infinies
     * lors de la sérialisation (le convoi contient des participations qui contiennent
     * le convoi qui contient des participations... etc.).
     */
    #[ORM\ManyToOne(inversedBy: 'memberships')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['convoy_participation:write'])]
    private ?Convoy $convoyID = null;

    /**
     * L'utilisateur qui participe à ce convoi.
     *
     * Ce champ est visible en lecture ET écriture : quand on crée une participation,
     * on envoie l'ID de l'utilisateur, et quand on lit on récupère ses infos.
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['convoy_participation:read', 'convoy_participation:write'])]
    private ?User $userID = null;

    /**
     * Date et heure à laquelle l'utilisateur a rejoint le convoi.
     *
     * Ce champ est en lecture seule : il est défini automatiquement dans le constructeur
     * lors de la création de l'objet (voir ci-dessous). L'utilisateur ne peut pas
     * le modifier via l'API.
     */
    #[ORM\Column]
    #[Groups(['convoy_participation:read'])]
    private ?\DateTimeImmutable $joinedAt = null;

    /**
     * Constructeur : initialise automatiquement la date d'inscription.
     *
     * "new \DateTimeImmutable()" crée un objet représentant la date et l'heure actuelles.
     * Ainsi, dès qu'une participation est créée (new ConvoyParticipation()),
     * la date d'inscription est automatiquement enregistrée sans intervention manuelle.
     */
    public function __construct()
    {
        $this->joinedAt = new \DateTimeImmutable();
    }

    /**
     * Retourne l'identifiant unique de la participation.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne le convoi associé à cette participation.
     */
    public function getConvoyID(): ?Convoy
    {
        return $this->convoyID;
    }

    /**
     * Associe cette participation à un convoi.
     */
    public function setConvoyID(?Convoy $convoyID): static
    {
        $this->convoyID = $convoyID;
        return $this;
    }

    /**
     * Retourne l'utilisateur inscrit à ce convoi.
     */
    public function getUserID(): ?User
    {
        return $this->userID;
    }

    /**
     * Définit l'utilisateur inscrit à ce convoi.
     */
    public function setUserID(?User $userID): static
    {
        $this->userID = $userID;
        return $this;
    }

    /**
     * Retourne la date et l'heure d'inscription au convoi.
     * Pas de setter pour ce champ : la date est fixée une fois pour toutes dans le constructeur.
     */
    public function getJoinedAt(): ?\DateTimeImmutable
    {
        return $this->joinedAt;
    }
}
