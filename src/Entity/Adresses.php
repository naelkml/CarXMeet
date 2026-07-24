<?php

/**
 * Entité Adresses - Représente un lieu ou un spot connu dans l'application CarXMeet.
 *
 * Qu'est-ce qu'une "entité" ?
 * En programmation orientée objet avec Symfony, une entité est une classe PHP qui représente
 * une table dans la base de données. Chaque propriété de la classe correspond à une colonne
 * dans la table. C'est ce qu'on appelle un ORM (Object-Relational Mapping) : le framework
 * fait le lien entre les objets PHP et les lignes de la base de données automatiquement.
 *
 * Cette entité représente les adresses/spots (circuits, parkings, points de ralliement, etc.)
 * que les utilisateurs peuvent consulter sur l'application, regroupés par région.
 */

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AdressesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;

/**
 * L'annotation #[ORM\Entity] indique à Doctrine (l'ORM de Symfony) que cette classe
 * correspond à une table en base de données. Le "repositoryClass" pointe vers la classe
 * qui contient les requêtes personnalisées pour récupérer des adresses.
 *
 * L'annotation #[ApiResource] expose automatiquement cette entité via une API REST.
 * Cela signifie que des applications front-end (React, mobile...) peuvent récupérer
 * les données via des requêtes HTTP.
 * - GetCollection : permet de récupérer TOUTES les adresses (liste)
 * - Get : permet de récupérer UNE adresse par son identifiant
 *
 * Les "groups" (groupes de sérialisation) contrôlent quels champs sont visibles
 * en lecture ('adresse:read') et lesquels peuvent être modifiés en écriture ('adresse:write').
 */
#[ORM\Entity(repositoryClass: AdressesRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['adresse:read']]),
        new Get(normalizationContext: ['groups' => ['adresse:read']]),
    ],
    normalizationContext: ['groups' => ['adresse:read']],
    denormalizationContext: ['groups' => ['adresse:write']]
)]
class Adresses
{
    /**
     * Identifiant unique de l'adresse en base de données.
     *
     * #[ORM\Id] : indique que c'est la clé primaire de la table (identifiant unique)
     * #[ORM\GeneratedValue] : la valeur est générée automatiquement par la BDD (auto-incrément : 1, 2, 3...)
     * #[ORM\Column] : cette propriété correspond à une colonne dans la table
     * ?int : le "?" signifie que la valeur peut être null (avant que l'objet soit sauvegardé en BDD)
     * #[Groups] : ce champ sera inclus dans les réponses API en lecture
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['adresse:read'])]
    private ?int $id = null;

    /**
     * Nom ou titre du lieu (ex : "Circuit de Pau", "Parking du Lac").
     *
     * length: 255 signifie que le champ peut contenir jusqu'à 255 caractères.
     * Ce champ est visible en lecture ET modifiable en écriture via l'API.
     */
    #[ORM\Column(length: 255)]
    #[Groups(['adresse:read', 'adresse:write'])]
    private ?string $title = null;

    /**
     * Description détaillée du lieu (texte long).
     *
     * Types::TEXT permet de stocker de grands textes, sans limite de 255 caractères.
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['adresse:read', 'adresse:write'])]
    private ?string $description = null;

    /**
     * Chemin ou URL de la photo illustrant le lieu.
     *
     * On stocke uniquement le nom/chemin du fichier (une chaîne de caractères),
     * pas l'image elle-même. L'image est stockée sur le serveur ou un service externe.
     */
    #[ORM\Column(length: 255)]
    #[Groups(['adresse:read', 'adresse:write'])]
    private ?string $photo = null;

    /**
     * URL du site web associé au lieu (facultatif).
     */
    #[ORM\Column(length: 255)]
    #[Groups(['adresse:read', 'adresse:write'])]
    private ?string $websiteUrl = null;

    /**
     * Relation vers la région à laquelle appartient ce lieu.
     *
     * #[ORM\ManyToOne] : c'est une relation "Plusieurs adresses pour une région".
     * Concrètement, plusieurs adresses peuvent appartenir à la même région (ex : Île-de-France),
     * mais une adresse n'appartient qu'à une seule région.
     * "inversedBy: 'adresses'" indique que dans la classe Region, il y a une propriété $adresses
     * qui liste toutes les adresses de cette région.
     * ?Region : c'est un objet de type Region (ou null si non défini).
     */
    #[ORM\ManyToOne(inversedBy: 'adresses')]
    #[Groups(['adresse:read', 'adresse:write'])]
    private ?Region $regionID = null;

    // ===========================================================================
    // GETTERS ET SETTERS
    // ===========================================================================
    // Les getters (get...) permettent de LIRE la valeur d'une propriété privée.
    // Les setters (set...) permettent de MODIFIER la valeur d'une propriété privée.
    // Les propriétés sont privées (private) par sécurité : on ne peut pas y accéder
    // directement depuis l'extérieur de la classe. On passe obligatoirement par ces méthodes.
    // ===========================================================================

    /**
     * Retourne l'identifiant unique de l'adresse.
     * Renvoie null si l'objet n'a pas encore été sauvegardé en base de données.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne le titre/nom du lieu.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Définit le titre/nom du lieu.
     *
     * "return $this" (aussi appelé "fluent interface") permet de chaîner les appels :
     * ex : $adresse->setTitle('Circuit')->setDescription('...')
     * "static" signifie que la méthode retourne le même type que la classe appelante.
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Retourne la description textuelle du lieu.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Définit la description du lieu.
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Retourne le chemin/URL de la photo du lieu.
     */
    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    /**
     * Définit la photo du lieu.
     */
    public function setPhoto(string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Retourne l'URL du site web associé au lieu.
     */
    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    /**
     * Définit l'URL du site web du lieu.
     */
    public function setWebsiteUrl(string $websiteUrl): static
    {
        $this->websiteUrl = $websiteUrl;

        return $this;
    }

    /**
     * Retourne l'objet Region auquel appartient cette adresse.
     * Peut retourner null si aucune région n'est associée.
     */
    public function getRegionID(): ?Region
    {
        return $this->regionID;
    }

    /**
     * Associe cette adresse à une région.
     * Le paramètre accepte null pour dissocier l'adresse de toute région.
     */
    public function setRegionID(?Region $regionID): static
    {
        $this->regionID = $regionID;

        return $this;
    }
}
