<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\FriendshipRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;

#[ORM\Entity(repositoryClass: FriendshipRepository::class)]

#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['friendship:read']]),
        new Get(normalizationContext: ['groups' => ['friendship:read']]),
        new Post(denormalizationContext: ['groups' => ['friendship:write']]),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['friendship:read']],
    denormalizationContext: ['groups' => ['friendship:write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['requesterId' => 'exact', 'receiverId' => 'exact', 'status' => 'exact'])]
class Friendship
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['friendship:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'friendships')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['friendship:read', 'friendship:write'])]
    private ?User $requesterId = null;

    #[ORM\ManyToOne(inversedBy: 'friendships_receiver')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['friendship:read', 'friendship:write'])]
    private ?User $receiverId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['friendship:read', 'friendship:write'])]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequesterId(): ?User
    {
        return $this->requesterId;
    }

    public function setRequesterId(?User $requesterId): static
    {
        $this->requesterId = $requesterId;
        return $this;
    }

    public function getReceiverId(): ?User
    {
        return $this->receiverId;
    }

    public function setReceiverId(?User $receiverId): static
    {
        $this->receiverId = $receiverId;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
