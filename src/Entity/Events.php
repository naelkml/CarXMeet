<?php

namespace App\Entity;

use App\Repository\EventsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventsRepository::class)]
class Events
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: 'blob', nullable: true)]
    private $coverPhoto;

    #[ORM\Column(length: 255)]
    private ?string $gallery = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $Date = null;

    #[ORM\Column(length: 255)]
    private ?string $location = null;

    #[ORM\Column(length: 255)]
    private ?string $ratingAverage = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $organisateur = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?Region $regionID = null;

    /**
     * @var Collection<int, Convoy>
     */
    #[ORM\OneToMany(targetEntity: Convoy::class, mappedBy: 'eventID')]
    private Collection $convoys;

    /**
     * @var Collection<int, Participation>
     */
    #[ORM\OneToMany(targetEntity: Participation::class, mappedBy: 'eventID')]
    private Collection $participations;

    public function __construct()
    {
        $this->convoys = new ArrayCollection();
        $this->participations = new ArrayCollection();
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

    public function setGallery(string $gallery): static
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
            // set the owning side to null (unless already changed)
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
            // set the owning side to null (unless already changed)
            if ($participation->getEventID() === $this) {
                $participation->setEventID(null);
            }
        }

        return $this;
    }
}
