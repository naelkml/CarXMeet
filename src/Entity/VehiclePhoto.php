<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\VehiclePhotoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: VehiclePhotoRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['vehicle_photo:read']],
    denormalizationContext: ['groups' => ['vehicle_photo:write']]
)]
class VehiclePhoto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['vehicle_photo:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'galleryPhotos')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['vehicle_photo:read', 'vehicle_photo:write'])]
    private ?Vehicle $vehicleID = null;

    #[ORM\Column(type: 'blob')]
    private $photo;

    #[ORM\Column]
    #[Groups(['vehicle_photo:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVehicleID(): ?Vehicle
    {
        return $this->vehicleID;
    }

    public function setVehicleID(?Vehicle $vehicleID): static
    {
        $this->vehicleID = $vehicleID;
        return $this;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo): static
    {
        $this->photo = $photo;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[Groups(['vehicle_photo:read'])]
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
