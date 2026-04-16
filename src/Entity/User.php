<?php

namespace App\Entity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
# [ORM\EntityListeners('App\EventListener\UserListener')]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private string $password;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $snapchat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $instagram = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $twitter = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tiktok = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Crew>
     */
    #[ORM\ManyToMany(targetEntity: Crew::class, mappedBy: 'members')]
    private Collection $crewID;

    /**
     * @var Collection<int, Friendship>
     */
    #[ORM\OneToMany(mappedBy: 'requesterId', targetEntity: Friendship::class)]
    private Collection $friendships;

    /**
     * @var Collection<int, Friendship>
     */
    #[ORM\OneToMany(mappedBy: 'receiverId', targetEntity: Friendship::class)]
    private Collection $friendships_receiver;

    /**
     * @var Collection<int, Vehicle>
     */
    #[ORM\OneToMany(targetEntity: Vehicle::class, mappedBy: 'userID')]
    private Collection $vehicles;

    /**
     * @var Collection<int, Postphoto>
     */
    #[ORM\OneToMany(targetEntity: Postphoto::class, mappedBy: 'userID')]
    private Collection $postphotos;

    /**
     * @var Collection<int, Participation>
     */
    #[ORM\OneToMany(targetEntity: Participation::class, mappedBy: 'userID')]
    private Collection $participations;

    /**
     * @var Collection<int, Articles>
     */
    #[ORM\OneToMany(targetEntity: Articles::class, mappedBy: 'userID')]
    private Collection $articles;

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $confirmationToken = null;

    #[ORM\Column(type: 'blob', nullable: true)]
    private $profilePhoto = null;

    public function __construct()
    {
        $this->crewID = new ArrayCollection();
        $this->friendships = new ArrayCollection();
        $this->friendships_receiver = new ArrayCollection();
        $this->vehicles = new ArrayCollection();
        $this->postphotos = new ArrayCollection();
        $this->participations = new ArrayCollection();
        $this->articles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getSnapchat(): ?string
    {
        return $this->snapchat;
    }

    public function setSnapchat(?string $snapchat): static
    {
        $this->snapchat = $snapchat;

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): static
    {
        $this->instagram = $instagram;

        return $this;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): static
    {
        $this->twitter = $twitter;

        return $this;
    }

    public function getTiktok(): ?string
    {
        return $this->tiktok;
    }

    public function setTiktok(?string $tiktok): static
    {
        $this->tiktok = $tiktok;

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

    /**
     * @return Collection<int, Crew>
     */
    public function getCrewID(): Collection
    {
        return $this->crewID;
    }

    public function addCrewID(Crew $crewID): static
    {
        if (!$this->crewID->contains($crewID)) {
            $this->crewID->add($crewID);
            $crewID->addMember($this);
        }

        return $this;
    }

    public function removeCrewID(Crew $crewID): static
    {
        if ($this->crewID->removeElement($crewID)) {
            $crewID->removeMember($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Friendship>
     */
    public function getFriendships(): Collection
    {
        return $this->friendships;
    }

    public function addFriendship(Friendship $friendship): static
    {
        if (!$this->friendships->contains($friendship)) {
            $this->friendships->add($friendship);
            $friendship->setRequesterId($this);
        }

        return $this;
    }

    public function removeFriendship(Friendship $friendship): static
    {
        if ($this->friendships->removeElement($friendship)) {
            if ($friendship->getRequesterId() === $this) {
                $friendship->setRequesterId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Friendship>
     */
    public function getFriendshipsReceiver(): Collection
    {
        return $this->friendships_receiver;
    }

    public function addFriendshipsReceiver(Friendship $friendshipsReceiver): static
    {
        if (!$this->friendships_receiver->contains($friendshipsReceiver)) {
            $this->friendships_receiver->add($friendshipsReceiver);
            $friendshipsReceiver->setReceiverId($this);
        }

        return $this;
    }

    public function removeFriendshipsReceiver(Friendship $friendshipsReceiver): static
    {
        if ($this->friendships_receiver->removeElement($friendshipsReceiver)) {
            if ($friendshipsReceiver->getReceiverId() === $this) {
                $friendshipsReceiver->setReceiverId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Vehicle>
     */
    public function getVehicles(): Collection
    {
        return $this->vehicles;
    }

    public function addVehicle(Vehicle $vehicle): static
    {
        if (!$this->vehicles->contains($vehicle)) {
            $this->vehicles->add($vehicle);
            $vehicle->setUserID($this);
        }

        return $this;
    }

    public function removeVehicle(Vehicle $vehicle): static
    {
        if ($this->vehicles->removeElement($vehicle)) {
            // set the owning side to null (unless already changed)
            if ($vehicle->getUserID() === $this) {
                $vehicle->setUserID(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Postphoto>
     */
    public function getPostphotos(): Collection
    {
        return $this->postphotos;
    }

    public function addPostphoto(Postphoto $postphoto): static
    {
        if (!$this->postphotos->contains($postphoto)) {
            $this->postphotos->add($postphoto);
            $postphoto->setUserID($this);
        }

        return $this;
    }

    public function removePostphoto(Postphoto $postphoto): static
    {
        if ($this->postphotos->removeElement($postphoto)) {
            // set the owning side to null (unless already changed)
            if ($postphoto->getUserID() === $this) {
                $postphoto->setUserID(null);
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
            $participation->setUserID($this);
        }

        return $this;
    }

    public function removeParticipation(Participation $participation): static
    {
        if ($this->participations->removeElement($participation)) {
            // set the owning side to null (unless already changed)
            if ($participation->getUserID() === $this) {
                $participation->setUserID(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Articles>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Articles $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setUserID($this);
        }

        return $this;
    }

    public function removeArticle(Articles $article): static
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getUserID() === $this) {
                $article->setUserID(null);
            }
        }

        return $this;
    }
    public function getUserIdentifier(): string
    {
        // The firewall/provider is configured to load users by "username".
        return (string) $this->username;
    }

    public function getRoles(): array
    {
        return $this->roles ?: ['ROLE_USER'];
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;
        return $this;
    }

    public function getProfilePhoto()
    {
        return $this->profilePhoto;
    }

    public function setProfilePhoto($profilePhoto): static
    {
        $this->profilePhoto = $profilePhoto;
        return $this;
    }

    public function getProfileImageBase64(): ?string
    {
        if (!$this->profilePhoto) {
            return null;
        }

        if (is_resource($this->profilePhoto)) {
            $meta = stream_get_meta_data($this->profilePhoto);
            if (!empty($meta['seekable'])) {
                rewind($this->profilePhoto);
            }
            $data = stream_get_contents($this->profilePhoto);
        } else {
            $data = $this->profilePhoto;
        }

        return base64_encode($data);
    }

}
