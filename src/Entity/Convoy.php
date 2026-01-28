<?php

namespace App\Entity;

use App\Repository\ConvoyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConvoyRepository::class)]
class Convoy
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'convoys')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Events $eventID = null;

    #[ORM\Column(length: 255)]
    private ?string $departureLocation = null;

    #[ORM\Column(length: 255)]
    private ?string $departureTime = null;

    #[ORM\Column(length: 255)]
    private ?string $participants = null;

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

    public function getParticipants(): ?string
    {
        return $this->participants;
    }

    public function setParticipants(string $participants): static
    {
        $this->participants = $participants;

        return $this;
    }
}
