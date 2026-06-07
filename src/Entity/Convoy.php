<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ConvoyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\Convoy\CreateConvoyController;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;

#[ORM\Entity(repositoryClass: ConvoyRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['convoy:read', 'convoy_participation:read', 'user:read']]),
        new Get(normalizationContext: ['groups' => ['convoy:read', 'convoy_participation:read', 'user:read']]),
        new Post(
            controller: CreateConvoyController::class,
            deserialize: false,
            read: false,
            normalizationContext: ['groups' => ['convoy:read']],
        ),
    ],
    normalizationContext: ['groups' => ['convoy:read']],
    denormalizationContext: ['groups' => ['convoy:write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['eventID' => 'exact'])]
class Convoy
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['convoy:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'convoys')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['convoy:read', 'convoy:write'])]
    private ?Events $eventID = null;

    #[ORM\Column(length: 255)]
    #[Groups(['convoy:read', 'convoy:write'])]
    private ?string $departureLocation = null;

    #[ORM\Column(length: 255)]
    #[Groups(['convoy:read', 'convoy:write'])]
    private ?string $departureTime = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['convoy:read', 'convoy:write'])]
    private ?string $departureDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['convoy:read', 'convoy:write'])]
    private ?string $participants = null;

    /**
     * @var Collection<int, ConvoyParticipation>
     */
    #[ORM\OneToMany(targetEntity: ConvoyParticipation::class, mappedBy: 'convoyID', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['convoy:read'])]
    private Collection $memberships;

    public function __construct()
    {
        $this->memberships = new ArrayCollection();
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

    public function getDepartureLocation(): ?string
    {
        return $this->departureLocation;
    }

    public function setDepartureLocation(string $departureLocation): static
    {
        $this->departureLocation = $departureLocation;

        return $this;
    }

    public function getDepartureTime(): ?string
    {
        return $this->departureTime;
    }

    public function setDepartureTime(string $departureTime): static
    {
        $this->departureTime = $departureTime;

        return $this;
    }

    public function getDepartureDate(): ?string
    {
        return $this->departureDate;
    }

    public function setDepartureDate(?string $departureDate): static
    {
        $this->departureDate = $departureDate;
        return $this;
    }

    public function getParticipants(): ?string
    {
        return $this->participants;
    }

    public function setParticipants(?string $participants): static
    {
        $this->participants = $participants;

        return $this;
    }

    /**
     * @return Collection<int, ConvoyParticipation>
     */
    public function getMemberships(): Collection
    {
        return $this->memberships;
    }

    public function addMembership(ConvoyParticipation $membership): static
    {
        if (!$this->memberships->contains($membership)) {
            $this->memberships->add($membership);
            $membership->setConvoyID($this);
        }

        return $this;
    }

    public function removeMembership(ConvoyParticipation $membership): static
    {
        if ($this->memberships->removeElement($membership)) {
            // orphanRemoval handles the delete; avoid setting FK to null (JoinColumn is non-nullable).
        }

        return $this;
    }
}
