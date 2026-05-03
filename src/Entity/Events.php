<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\EventsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EventsRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['event:read']],
    denormalizationContext: ['groups' => ['event:write']]
)]
class Events
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['event:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['event:read', 'event:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['event:read', 'event:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'blob', nullable: true)]
    private $coverPhoto;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['event:read', 'event:write'])]
    private ?string $gallery = null;

    #[ORM\Column(length: 255)]
    #[Groups(['event:read', 'event:write'])]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Groups(['event:read', 'event:write'])]
    private ?string $Date = null;

    #[ORM\Column(length: 255)]
    #[Groups(['event:read', 'event:write'])]
    private ?string $location = null;

    #[ORM\Column(length: 255)]
    #[Groups(['event:read'])]
    private ?string $ratingAverage = null;

    #[ORM\Column]
    #[Groups(['event:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['event:read', 'event:write'])]
    private ?string $organisateur = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[Groups(['event:read', 'event:write'])]
    private ?Region $regionID = null;

    /**
     * @var Collection<int, Convoy>
     */
    #[ORM\OneToMany(targetEntity: Convoy::class, mappedBy: 'eventID')]
    #[Groups(['event:read'])]
    private Collection $convoys;

    /**
     * @var Collection<int, Participation>
     */
    #[ORM\OneToMany(targetEntity: Participation::class, mappedBy: 'eventID')]
    #[Groups(['event:read'])]
    private Collection $participations;

    /**
     * @var Collection<int, EventPhoto>
     */
    #[ORM\OneToMany(targetEntity: EventPhoto::class, mappedBy: 'eventID', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['event:read'])]
    private Collection $galleryPhotos;

    /**
     * @var Collection<int, EventRating>
     */
    #[ORM\OneToMany(targetEntity: EventRating::class, mappedBy: 'eventID', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['event:read'])]
    private Collection $ratings;

    public function __construct()
    {
        $this->convoys = new ArrayCollection();
        $this->participations = new ArrayCollection();
        $this->galleryPhotos = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

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

    public function getOrganisateur(): ?string
    {
        return $this->organisateur;
    }

    public function setOrganisateur(string $organisateur): static
    {
        $this->organisateur = $organisateur;
        return $this;
    }


    public function getCoverPhoto()
    {
        return $this->coverPhoto;
    }

    public function setCoverPhoto($coverPhoto): self
    {
        $this->coverPhoto = $coverPhoto;
        return $this;
    }

    #[Groups(['event:read'])]
    public function getImageBase64(): ?string
    {
        if (!$this->coverPhoto) {
            return null;
        }

        if (is_resource($this->coverPhoto)) {
            $meta = stream_get_meta_data($this->coverPhoto);
            if (!empty($meta['seekable'])) {
                rewind($this->coverPhoto);
            }
            $data = stream_get_contents($this->coverPhoto);
        } else {
            $data = $this->coverPhoto;
        }

        return base64_encode($data);
    }

    public function getGallery(): ?string
    {
        return $this->gallery;
    }

    public function setGallery(?string $gallery): static
    {
        $this->gallery = $gallery;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->Date;
    }

    public function setDate(string $Date): static
    {
        $this->Date = $Date;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getRatingAverage(): ?string
    {
        return $this->ratingAverage;
    }

    public function setRatingAverage(string $ratingAverage): static
    {
        $this->ratingAverage = $ratingAverage;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    /**
     * @return Collection<int, Convoy>
     */
    public function getConvoys(): Collection
    {
        return $this->convoys;
    }

    public function addConvoy(Convoy $convoy): static
    {
        if (!$this->convoys->contains($convoy)) {
            $this->convoys->add($convoy);
            $convoy->setEventID($this);
        }

        return $this;
    }

    public function removeConvoy(Convoy $convoy): static
    {
        if ($this->convoys->removeElement($convoy)) {
            if ($convoy->getEventID() === $this) {
                $convoy->setEventID(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Participation>
     */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    /**
     * @return Collection<int, EventPhoto>
     */
    public function getGalleryPhotos(): Collection
    {
        return $this->galleryPhotos;
    }

    public function addGalleryPhoto(EventPhoto $photo): static
    {
        if (!$this->galleryPhotos->contains($photo)) {
            $this->galleryPhotos->add($photo);
            $photo->setEventID($this);
        }

        return $this;
    }

    public function removeGalleryPhoto(EventPhoto $photo): static
    {
        if ($this->galleryPhotos->removeElement($photo)) {
            if ($photo->getEventID() === $this) {
                $photo->setEventID(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EventRating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(EventRating $rating): static
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setEventID($this);
        }

        return $this;
    }

    public function removeRating(EventRating $rating): static
    {
        if ($this->ratings->removeElement($rating)) {
            if ($rating->getEventID() === $this) {
                $rating->setEventID(null);
            }
        }

        return $this;
    }

    public function addParticipation(Participation $participation): static
    {
        if (!$this->participations->contains($participation)) {
            $this->participations->add($participation);
            $participation->setEventID($this);
        }

        return $this;
    }

    public function removeParticipation(Participation $participation): static
    {
        if ($this->participations->removeElement($participation)) {
            if ($participation->getEventID() === $this) {
                $participation->setEventID(null);
            }
        }

        return $this;
    }
}
