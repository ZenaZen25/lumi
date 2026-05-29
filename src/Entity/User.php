<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $codeClasse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 20)]
    private ?string $role = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isAnonymous = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $lastLogin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sessionToken = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Etablissement $etablissement = null;

    /**
     * @var Collection<int, Signalement>
     */
    #[ORM\OneToMany(targetEntity: Signalement::class, mappedBy: 'user')]
    private Collection $signalements;

    /**
     * @var Collection<int, Conversation>
     */
    #[ORM\OneToMany(targetEntity: Conversation::class, mappedBy: 'user')]
    private Collection $conversations;

    /**
     * @var Collection<int, UserBadge>
     */
    #[ORM\OneToMany(targetEntity: UserBadge::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $userBadges;

    /**
     * @var Collection<int, CouragePoint>
     */
    #[ORM\OneToMany(targetEntity: CouragePoint::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $couragePoints;

    /**
     * @var Collection<int, Alerte>
     */
    #[ORM\OneToMany(targetEntity: Alerte::class, mappedBy: 'treatedBy')]
    private Collection $alertesTraitees;

    public function __construct()
    {
        $this->signalements = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->userBadges = new ArrayCollection();
        $this->couragePoints = new ArrayCollection();
        $this->alertesTraitees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getCodeClasse(): ?string
    {
        return $this->codeClasse;
    }

    public function setCodeClasse(?string $codeClasse): static
    {
        $this->codeClasse = $codeClasse;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function isAnonymous(): ?bool
    {
        return $this->isAnonymous;
    }

    public function setIsAnonymous(?bool $isAnonymous): static
    {
        $this->isAnonymous = $isAnonymous;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTime $lastLogin): static
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    public function getSessionToken(): ?string
    {
        return $this->sessionToken;
    }

    public function setSessionToken(?string $sessionToken): static
    {
        $this->sessionToken = $sessionToken;
        return $this;
    }

    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function setEtablissement(?Etablissement $etablissement): static
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * @return Collection<int, Signalement>
     */
    public function getSignalements(): Collection
    {
        return $this->signalements;
    }

    public function addSignalement(Signalement $signalement): static
    {
        if (!$this->signalements->contains($signalement)) {
            $this->signalements->add($signalement);
            $signalement->setUser($this);
        }
        return $this;
    }

    public function removeSignalement(Signalement $signalement): static
    {
        if ($this->signalements->removeElement($signalement)) {
            if ($signalement->getUser() === $this) {
                $signalement->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): static
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations->add($conversation);
            $conversation->setUser($this);
        }
        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        if ($this->conversations->removeElement($conversation)) {
            if ($conversation->getUser() === $this) {
                $conversation->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, UserBadge>
     */
    public function getUserBadges(): Collection
    {
        return $this->userBadges;
    }

    public function addUserBadge(UserBadge $userBadge): static
    {
        if (!$this->userBadges->contains($userBadge)) {
            $this->userBadges->add($userBadge);
            $userBadge->setUser($this);
        }
        return $this;
    }

    public function removeUserBadge(UserBadge $userBadge): static
    {
        if ($this->userBadges->removeElement($userBadge)) {
            if ($userBadge->getUser() === $this) {
                $userBadge->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, CouragePoint>
     */
    public function getCouragePoints(): Collection
    {
        return $this->couragePoints;
    }

    public function addCouragePoint(CouragePoint $couragePoint): static
    {
        if (!$this->couragePoints->contains($couragePoint)) {
            $this->couragePoints->add($couragePoint);
            $couragePoint->setUser($this);
        }
        return $this;
    }

    public function removeCouragePoint(CouragePoint $couragePoint): static
    {
        if ($this->couragePoints->removeElement($couragePoint)) {
            if ($couragePoint->getUser() === $this) {
                $couragePoint->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Alerte>
     */
    public function getAlertesTraitees(): Collection
    {
        return $this->alertesTraitees;
    }

    public function addAlertesTraitee(Alerte $alertesTraitee): static
    {
        if (!$this->alertesTraitees->contains($alertesTraitee)) {
            $this->alertesTraitees->add($alertesTraitee);
            $alertesTraitee->setTreatedBy($this);
        }
        return $this;
    }

    public function removeAlertesTraitee(Alerte $alertesTraitee): static
    {
        if ($this->alertesTraitees->removeElement($alertesTraitee)) {
            if ($alertesTraitee->getTreatedBy() === $this) {
                $alertesTraitee->setTreatedBy(null);
            }
        }
        return $this;
    }

    // ---- UserInterface ----

    public function getUserIdentifier(): string
    {
        return (string) $this->prenom;
    }

    public function getRoles(): array
    {
        return ['ROLE_' . strtoupper($this->role ?? 'ELEVE')];
    }

    public function eraseCredentials(): void
    {
        // nothing
    }
}