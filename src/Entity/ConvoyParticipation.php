<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ConvoyParticipationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ConvoyParticipationRepository::class)]
#[ORM\Table(name: 'convoy_participation')]
#[ORM\UniqueConstraint(name: 'uniq_convoy_user', columns: ['convoy_id_id', 'user_id_id'])]
#[ApiResource(
    normalizationContext: ['groups' => ['convoy_participation:read']],
    denormalizationContext: ['groups' => ['convoy_participation:write']]
)]
class ConvoyParticipation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['convoy_participation:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'memberships')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['convoy_participation:read', 'convoy_participation:write'])]
    private ?Convoy $convoyID = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['convoy_participation:read', 'convoy_participation:write'])]
    private ?User $userID = null;

    #[ORM\Column]
    #[Groups(['convoy_participation:read'])]
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
