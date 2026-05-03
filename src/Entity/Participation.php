<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['participation:read']],
    denormalizationContext: ['groups' => ['participation:write']]
)]
class Participation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['participation:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['participation:read', 'participation:write'])]
    private ?\DateTimeImmutable $joinedAt = null;

    #[ORM\ManyToOne(inversedBy: 'participations')]
    #[Groups(['participation:read', 'participation:write'])]
    private ?User $userID = null;

    #[ORM\ManyToOne(inversedBy: 'participations')]
    #[Groups(['participation:read', 'participation:write'])]
    private ?Events $eventID = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJoinedAt(): ?\DateTimeImmutable
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(\DateTimeImmutable $joinedAt): static
    {
        $this->joinedAt = $joinedAt;

        return $this;
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

    public function getEventID(): ?Events
    {
        return $this->eventID;
    }

    public function setEventID(?Events $eventID): static
    {
        $this->eventID = $eventID;

        return $this;
    }
}
