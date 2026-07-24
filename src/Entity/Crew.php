<?php

/**
 * Entité Crew - Représente un groupe/équipe de passionnés automobiles.
 *
 * Un "crew" est une association informelle de membres partageant la même passion
 * pour l'automobile. Chaque crew a un nom, une description, un logo, et
 * un créateur (le fondateur du crew).
 *
 * Un crew peut avoir plusieurs membres (relation ManyToMany avec User),
 * car un utilisateur peut aussi appartenir à plusieurs crews.
 *
 * Concept de relation ManyToMany :
 * Imagine une équipe de football : un joueur peut appartenir à plusieurs équipes
 * dans sa carrière, et une équipe peut avoir plusieurs joueurs. C'est exactement
 * ce principe appliqué ici entre les crews et les utilisateurs.
 * En base de données, cette relation crée une TABLE INTERMÉDIAIRE automatique.
 */

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CrewRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;

/**
 * #[ORM\Entity] : entité Doctrine avec son repository pour les requêtes personnalisées.
 *
 * L'API expose uniquement la lecture (GetCollection et Get).
 * La création et modification des crews se font probablement via des contrôleurs
 * personnalisés non listés ici.
 */
#[ORM\Entity(repositoryClass: CrewRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['crew:read']]),
        new Get(normalizationContext: ['groups' => ['crew:read']]),
    ],
    normalizationContext: ['groups' => ['crew:read']],
    denormalizationContext: ['groups' => ['crew:write']]
)]
class Crew
{
    /**
     * Identifiant unique du crew, auto-généré par la base de données.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['crew:read'])]
    private ?int $id = null;

    /**
     * Nom du crew (ex : "Les Pistons du Nord", "Street Kings 75").
     */
    #[ORM\Column(length: 255)]
    #[Groups(['crew:read', 'crew:write'])]
    private ?string $name = null;

    /**
     * Description du crew, ses valeurs, son histoire, sa ville...
     * Types::TEXT pour les textes longs.
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['crew:read', 'crew:write'])]
    private ?string $description = null;

    /**
     * Chemin ou URL du logo du crew.
     * nullable: true : le logo est optionnel, un crew peut ne pas en avoir.
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['crew:read', 'crew:write'])]
    private ?string $logo = null;

    /**
     * L'utilisateur fondateur/créateur du crew.
     *
     * #[ORM\OneToOne] : relation "Un pour Un". Un crew n'a qu'un seul créateur,
     * et un utilisateur ne peut créer qu'un seul crew via cette relation.
     * "cascade: ['persist', 'remove']" : si on sauvegarde ou supprime le crew,
     * l'action se propage automatiquement sur le créateur lié.
     * nullable: false : un crew doit obligatoirement avoir un créateur.
     */
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['crew:read', 'crew:write'])]
    private ?User $CreatedBy = null;

    /**
     * Liste des membres du crew.
     *
     * Collection<int, User> : une collection d'objets User.
     *
     * #[ORM\ManyToMany] : un crew peut avoir plusieurs membres,
     * et un utilisateur peut appartenir à plusieurs crews.
     * "inversedBy: 'crewID'" : dans la classe User, la propriété $crewID
     * liste tous les crews de cet utilisateur.
     * Doctrine crée automatiquement une table intermédiaire "crew_user" en BDD.
     */
    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'crewID')]
    #[Groups(['crew:read', 'crew:write'])]
    private Collection $members;

    /**
     * Date de création du crew.
     * En lecture seule : définie une fois lors de la création.
     */
    #[ORM\Column]
    #[Groups(['crew:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Constructeur : initialise la collection de membres avec un ArrayCollection vide.
     * Sans cette ligne, $members serait null et toute tentative d'ajouter un membre
     * provoquerait une erreur PHP.
     */
    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    /**
     * Retourne l'identifiant unique du crew.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne le nom du crew.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Définit le nom du crew.
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Retourne la description du crew.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Définit la description du crew.
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Retourne le chemin du logo du crew (peut être null).
     */
    public function getLogo(): ?string
    {
        return $this->logo;
    }

    /**
     * Définit le logo du crew. Accepte null pour le supprimer.
     */
    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Retourne l'utilisateur qui a créé ce crew.
     */
    public function getCreatedBy(): ?User
    {
        return $this->CreatedBy;
    }

    /**
     * Définit le créateur du crew.
     * Contrairement aux autres setters, le paramètre n'accepte pas null
     * car le créateur est obligatoire (JoinColumn non nullable).
     */
    public function setCreatedBy(User $CreatedBy): static
    {
        $this->CreatedBy = $CreatedBy;

        return $this;
    }

    /**
     * Retourne la collection de tous les membres du crew.
     *
     * @return Collection<int, User>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    /**
     * Ajoute un utilisateur comme membre du crew.
     *
     * On vérifie avec "contains" que l'utilisateur n'est pas déjà membre.
     * Si ce n'est pas le cas, on l'ajoute à la collection.
     * Note : on ne synchronise pas l'autre côté ici (pas de $member->addCrewID($this))
     * car c'est géré dans la classe User.
     */
    public function addMember(User $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }

    /**
     * Retire un utilisateur de la liste des membres du crew.
     * "removeElement" supprime l'élément de la collection si il s'y trouve.
     */
    public function removeMember(User $member): static
    {
        $this->members->removeElement($member);

        return $this;
    }

    /**
     * Retourne la date de création du crew.
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Définit la date de création du crew.
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
