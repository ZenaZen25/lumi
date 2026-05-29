<?php

namespace App\Entity;

use App\Repository\EtablissementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EtablissementRepository::class)]
class Etablissement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $codeUai = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $emailContact = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'etablissement')]
    private Collection $users;

    /**
     * @var Collection<int, Signalement>
     */
    #[ORM\OneToMany(targetEntity: Signalement::class, mappedBy: 'etablissement')]
    private Collection $signalements;

    /**
     * @var Collection<int, HeatmapZone>
     */
    #[ORM\OneToMany(targetEntity: HeatmapZone::class, mappedBy: 'etablissement')]
    private Collection $heatmapZones;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->signalements = new ArrayCollection();
        $this->heatmapZones = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCodeUai(): ?string
    {
        return $this->codeUai;
    }

    public function setCodeUai(?string $codeUai): static
    {
        $this->codeUai = $codeUai;

        return $this;
    }

    public function getEmailContact(): ?string
    {
        return $this->emailContact;
    }

    public function setEmailContact(?string $emailContact): static
    {
        $this->emailContact = $emailContact;

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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setEtablissement($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getEtablissement() === $this) {
                $user->setEtablissement(null);
            }
        }

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
            $signalement->setEtablissement($this);
        }

        return $this;
    }

    public function removeSignalement(Signalement $signalement): static
    {
        if ($this->signalements->removeElement($signalement)) {
            // set the owning side to null (unless already changed)
            if ($signalement->getEtablissement() === $this) {
                $signalement->setEtablissement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HeatmapZone>
     */
    public function getHeatmapZones(): Collection
    {
        return $this->heatmapZones;
    }

    public function addHeatmapZone(HeatmapZone $heatmapZone): static
    {
        if (!$this->heatmapZones->contains($heatmapZone)) {
            $this->heatmapZones->add($heatmapZone);
            $heatmapZone->setEtablissement($this);
        }

        return $this;
    }

    public function removeHeatmapZone(HeatmapZone $heatmapZone): static
    {
        if ($this->heatmapZones->removeElement($heatmapZone)) {
            // set the owning side to null (unless already changed)
            if ($heatmapZone->getEtablissement() === $this) {
                $heatmapZone->setEtablissement(null);
            }
        }

        return $this;
    }
}
