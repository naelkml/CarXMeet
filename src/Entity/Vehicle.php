<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\VehicleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['vehicle:read']],
    denormalizationContext: ['groups' => ['vehicle:write']]
)]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['vehicle:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'vehicles')]
    #[Groups(['vehicle:read', 'vehicle:write'])]
    private ?User $userID = null;

    #[ORM\Column(length: 255)]
    #[Groups(['vehicle:read', 'vehicle:write'])]
    private ?string $brand = null;

    #[ORM\Column(length: 255)]
    #[Groups(['vehicle:read', 'vehicle:write'])]
    private ?string $model = null;

    #[ORM\Column(length: 4)]
    #[Groups(['vehicle:read', 'vehicle:write'])]
    private ?string $year = null;

    #[ORM\Column(length: 255)]
    #[Groups(['vehicle:read', 'vehicle:write'])]
    private ?string $engine = null;

    #[ORM\Column(length: 255)]
    #[Groups(['vehicle:read', 'vehicle:write'])]
    private ?string $preparation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['vehicle:read', 'vehicle:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'blob', nullable: true)]
    private $photos;

    /**
     * @var Collection<int, VehiclePhoto>
     */
    #[ORM\OneToMany(targetEntity: VehiclePhoto::class, mappedBy: 'vehicleID', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['vehicle:read'])]
    private Collection $galleryPhotos;

    public function __construct()
    {
        $this->galleryPhotos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserID(): ?User
    {
        return $this->userID;
    }

    public function setUserID(?User $userID): static
    {
        $this->userID = $userID;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(string $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getEngine(): ?string
    {
        return $this->engine;
    }

    public function setEngine(string $engine): static
    {
        $this->engine = $engine;

        return $this;
    }

    public function getPreparation(): ?string
    {
        return $this->preparation;
    }

    public function setPreparation(string $preparation): static
    {
        $this->preparation = $preparation;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPhotos()
    {
        return $this->photos;
    }

    public function setPhotos($photos): self
    {
        $this->photos = $photos;
        return $this;
    }

    /**
     * @return Collection<int, VehiclePhoto>
     */
    public function getGalleryPhotos(): Collection
    {
        return $this->galleryPhotos;
    }

    public function addGalleryPhoto(VehiclePhoto $photo): static
    {
        if (!$this->galleryPhotos->contains($photo)) {
            $this->galleryPhotos->add($photo);
            $photo->setVehicleID($this);
        }

        return $this;
    }

    public function removeGalleryPhoto(VehiclePhoto $photo): static
    {
        if ($this->galleryPhotos->removeElement($photo)) {
            if ($photo->getVehicleID() === $this) {
                $photo->setVehicleID(null);
            }
        }

        return $this;
    }

    #[Groups(['vehicle:read'])]
    public function getImageBase64(): ?string
    {
        if (!$this->photos) {
            return null;
        }

        if (is_resource($this->photos)) {
            $meta = stream_get_meta_data($this->photos);
            if (!empty($meta['seekable'])) {
                rewind($this->photos);
            }
            $data = stream_get_contents($this->photos);
        } else {
            $data = $this->photos;
        }

        return base64_encode($data);
    }
}
