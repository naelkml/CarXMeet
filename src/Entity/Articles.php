<?php

/**
 * Entité Articles - Représente un article de blog ou de magazine publié sur CarXMeet.
 *
 * Cette entité stocke les articles rédigés par les utilisateurs de la plateforme.
 * Un article est composé d'un titre, d'une image de couverture, d'un résumé,
 * d'un contenu complet et d'une date de création. Il est toujours lié à un auteur (User).
 *
 * En base de données, cela correspond à la table "articles" avec une colonne par propriété.
 */

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ArticlesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;

/**
 * #[ORM\Entity] : Doctrine sait que cette classe est une entité (= une table en BDD).
 *
 * #[ApiResource] : API Platform crée automatiquement des routes HTTP pour cette entité.
 * - GetCollection : GET /api/articles → retourne tous les articles
 * - Get : GET /api/articles/{id} → retourne un article spécifique
 *
 * Les groupes de sérialisation :
 * - 'article:read' : champs exposés lors d'une lecture (GET)
 * - 'article:write' : champs acceptés lors d'une création/modification (POST/PATCH)
 */
#[ORM\Entity(repositoryClass: ArticlesRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['article:read']]),
        new Get(normalizationContext: ['groups' => ['article:read']]),
    ],
    normalizationContext: ['groups' => ['article:read']],
    denormalizationContext: ['groups' => ['article:write']]
)]
class Articles
{
    /**
     * Identifiant unique de l'article, généré automatiquement par la base de données.
     * Ce champ est en lecture seule (pas dans le groupe 'article:write').
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['article:read'])]
    private ?int $id = null;

    /**
     * Titre de l'article (ex : "Les meilleurs spots en Île-de-France").
     * Limité à 255 caractères. Visible et modifiable via l'API.
     */
    #[ORM\Column(length: 255)]
    #[Groups(['article:read', 'article:write'])]
    private ?string $title = null;

    /**
     * Chemin ou URL de la photo de couverture de l'article.
     * C'est l'image principale affichée en haut de l'article ou dans les vignettes.
     */
    #[ORM\Column(length: 255)]
    #[Groups(['article:read', 'article:write'])]
    private ?string $coverPhoto = null;

    /**
     * Résumé court de l'article (quelques phrases d'accroche).
     * Affiché dans les listes d'articles pour donner un aperçu du contenu.
     */
    #[ORM\Column(length: 255)]
    #[Groups(['article:read', 'article:write'])]
    private ?string $summary = null;

    /**
     * Contenu complet de l'article (texte long sans limite fixe).
     * Types::TEXT permet de stocker de longues chaînes de texte,
     * contrairement à length: 255 qui est limité.
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['article:read', 'article:write'])]
    private ?string $content = null;

    /**
     * Date et heure de création de l'article.
     *
     * \DateTimeImmutable est un objet PHP représentant une date/heure.
     * "Immutable" signifie qu'une fois créé, on ne peut pas modifier cet objet
     * (on doit en créer un nouveau si on veut changer la date).
     * Ce champ est en lecture seule : l'API ne permet pas de le modifier directement.
     */
    #[ORM\Column]
    #[Groups(['article:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Relation vers l'utilisateur qui a écrit cet article.
     *
     * #[ORM\ManyToOne] : relation "Plusieurs articles pour un utilisateur".
     * Un utilisateur peut écrire plusieurs articles, mais chaque article
     * n'a qu'un seul auteur.
     * "inversedBy: 'articles'" : dans la classe User, la propriété $articles
     * liste tous les articles écrits par cet utilisateur.
     */
    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[Groups(['article:read', 'article:write'])]
    private ?User $userID = null;

    // ===========================================================================
    // GETTERS ET SETTERS
    // Les getters lisent une propriété privée, les setters la modifient.
    // Cette convention est standard en PHP orienté objet.
    // ===========================================================================

    /**
     * Retourne l'identifiant unique de l'article.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne le titre de l'article.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Définit le titre de l'article.
     * Retourne $this pour permettre le chaînage de méthodes.
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Retourne le chemin de la photo de couverture.
     */
    public function getCoverPhoto(): ?string
    {
        return $this->coverPhoto;
    }

    /**
     * Définit la photo de couverture de l'article.
     */
    public function setCoverPhoto(string $coverPhoto): static
    {
        $this->coverPhoto = $coverPhoto;

        return $this;
    }

    /**
     * Retourne le résumé court de l'article.
     */
    public function getSummary(): ?string
    {
        return $this->summary;
    }

    /**
     * Définit le résumé de l'article.
     */
    public function setSummary(string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Retourne le contenu complet de l'article.
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Définit le contenu complet de l'article.
     */
    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Retourne la date de création de l'article.
     * Le "?" devant DateTimeImmutable signifie que la valeur peut être null.
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Définit la date de création de l'article.
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Retourne l'objet User (auteur) associé à cet article.
     */
    public function getUserID(): ?User
    {
        return $this->userID;
    }

    /**
     * Associe un utilisateur (auteur) à cet article.
     * Le "?" dans le paramètre permet de passer null pour dissocier l'auteur.
     */
    public function setUserID(?User $userID): static
    {
        $this->userID = $userID;

        return $this;
    }
}
