<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AdressesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;

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
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['adresse:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['adresse:read', 'adresse:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['adresse:read', 'adresse:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['adresse:read', 'adresse:write'])]
    private ?string $photo = null;

    #[ORM\Column(length: 255)]
    #[Groups(['adresse:read', 'adresse:write'])]
    private ?string $websiteUrl = null;

    #[ORM\ManyToOne(inversedBy: 'adresses')]
    #[Groups(['adresse:read', 'adresse:write'])]
    private ?Region $regionID = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(string $websiteUrl): static
    {
        $this->websiteUrl = $websiteUrl;

        return $this;
    }

    public function getRegionID(): ?Region
    {
        return $this->regionID;
    }

    public function setRegionID(?Region $regionID): static
    {
        $this->regionID = $regionID;

        return $this;
    }
}
