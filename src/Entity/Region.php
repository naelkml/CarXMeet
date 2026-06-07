<?php

namespace App\Entity;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\RegionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;

#[ORM\Entity(repositoryClass: RegionRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['region:read']]),
        new Get(normalizationContext: ['groups' => ['region:read']]),
    ],
    normalizationContext: ['groups' => ['region:read']],
    denormalizationContext: ['groups' => ['region:write']]
)]
class Region
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['region:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['region:read', 'region:write'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Events>
     */
    #[ORM\OneToMany(targetEntity: Events::class, mappedBy: 'regionID')]
    private Collection $events;

    /**
     * @var Collection<int, Adresses>
     */
    #[ORM\OneToMany(targetEntity: Adresses::class, mappedBy: 'regionID')]
    private Collection $adresses;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->adresses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Events>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Events $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setRegionID($this);
        }

        return $this;
    }

    public function removeEvent(Events $event): static
    {
        if ($this->events->removeElement($event)) {
            if ($event->getRegionID() === $this) {
                $event->setRegionID(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Adresses>
     */
    public function getAdresses(): Collection
    {
        return $this->adresses;
    }

    public function addAdress(Adresses $adress): static
    {
        if (!$this->adresses->contains($adress)) {
            $this->adresses->add($adress);
            $adress->setRegionID($this);
        }

        return $this;
    }

    public function removeAdress(Adresses $adress): static
    {
        if ($this->adresses->removeElement($adress)) {
            if ($adress->getRegionID() === $this) {
                $adress->setRegionID(null);
            }
        }

        return $this;
    }
}
