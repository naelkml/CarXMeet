<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PostphotoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PostphotoRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['postphoto:read']],
    denormalizationContext: ['groups' => ['postphoto:write']]
)]
class Postphoto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['postphoto:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'postphotos')]
    #[Groups(['postphoto:read', 'postphoto:write'])]
    private ?User $userID = null;

    #[ORM\Column(length: 255)]
    #[Groups(['postphoto:read', 'postphoto:write'])]
    private ?string $image = null;

    #[ORM\Column]
    #[Groups(['postphoto:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

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
}
