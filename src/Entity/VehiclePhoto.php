<?php

namespace App\Entity;

use App\Repository\VehiclePhotoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehiclePhotoRepository::class)]
class VehiclePhoto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'galleryPhotos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vehicle $vehicleID = null;

    #[ORM\Column(type: 'blob')]
    private $photo;

    #[ORM\Column]
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

