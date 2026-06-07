<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\EventRatingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
#[ORM\Entity(repositoryClass: EventRatingRepository::class)]
#[ORM\Table(name: 'event_rating', uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_event_user', columns: ['event_id_id', 'user_id_id'])])]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['event_rating:read']]),
        new Get(normalizationContext: ['groups' => ['event_rating:read']]),
        new Post(denormalizationContext: ['groups' => ['event_rating:write']]),
        new Patch(denormalizationContext: ['groups' => ['event_rating:write']]),
    ],
    normalizationContext: ['groups' => ['event_rating:read']],
    denormalizationContext: ['groups' => ['event_rating:write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['userID' => 'exact', 'eventID' => 'exact'])]

class EventRating
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['event_rating:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'ratings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['event_rating:read', 'event_rating:write'])]
    private ?Events $eventID = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['event_rating:read', 'event_rating:write'])]
    private ?User $userID = null;

    #[ORM\Column]
    #[Groups(['event_rating:read', 'event_rating:write'])]
    private ?int $rating = null;

    #[ORM\Column]
    #[Groups(['event_rating:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['event_rating:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
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

    public function getUserID(): ?User
    {
        return $this->userID;
    }

    public function setUserID(?User $userID): static
    {
        $this->userID = $userID;
        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
