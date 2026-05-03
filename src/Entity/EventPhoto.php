<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\EventPhotoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EventPhotoRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['event_photo:read']],
    denormalizationContext: ['groups' => ['event_photo:write']]
)]
class EventPhoto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['event_photo:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'galleryPhotos')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['event_photo:read', 'event_photo:write'])]
    private ?Events $eventID = null;

    #[ORM\Column(type: 'blob')]
    private $photo;

    #[ORM\Column]
    #[Groups(['event_photo:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventID(): ?Events
    {
        return $this->eventID;
    }

    public function setEventID(?Events $eventID): static
    {
        $this->eventID = $eventID;
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

    #[Groups(['event_photo:read'])]
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
