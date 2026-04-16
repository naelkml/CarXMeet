<?php

namespace App\Entity;

use App\Repository\ConvoyParticipationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConvoyParticipationRepository::class)]
#[ORM\Table(name: 'convoy_participation')]
#[ORM\UniqueConstraint(name: 'uniq_convoy_user', columns: ['convoy_id_id', 'user_id_id'])]
class ConvoyParticipation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'memberships')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Convoy $convoyID = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userID = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $joinedAt = null;

    public function __construct()
    {
        $this->joinedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConvoyID(): ?Convoy
    {
        return $this->convoyID;
    }

    public function setConvoyID(?Convoy $convoyID): static
    {
        $this->convoyID = $convoyID;
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

    public function getJoinedAt(): ?\DateTimeImmutable
    {
        return $this->joinedAt;
    }
}

