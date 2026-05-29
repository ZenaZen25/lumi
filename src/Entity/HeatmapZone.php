<?php

namespace App\Entity;

use App\Repository\HeatmapZoneRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HeatmapZoneRepository::class)]
class HeatmapZone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nomZone = null;

    #[ORM\Column(nullable: true)]
    private ?int $positionX = null;

    #[ORM\Column(nullable: true)]
    private ?int $positionY = null;

    #[ORM\Column(nullable: true)]
    private ?int $incidentCount = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $lastUpdated = null;

    #[ORM\ManyToOne(inversedBy: 'heatmapZones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etablissement $etablissement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomZone(): ?string
    {
        return $this->nomZone;
    }

    public function setNomZone(string $nomZone): static
    {
        $this->nomZone = $nomZone;

        return $this;
    }

    public function getPositionX(): ?int
    {
        return $this->positionX;
    }

    public function setPositionX(?int $positionX): static
    {
        $this->positionX = $positionX;

        return $this;
    }

    public function getPositionY(): ?int
    {
        return $this->positionY;
    }

    public function setPositionY(?int $positionY): static
    {
        $this->positionY = $positionY;

        return $this;
    }

    public function getIncidentCount(): ?int
    {
        return $this->incidentCount;
    }

    public function setIncidentCount(?int $incidentCount): static
    {
        $this->incidentCount = $incidentCount;

        return $this;
    }

    public function getLastUpdated(): ?\DateTime
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(?\DateTime $lastUpdated): static
    {
        $this->lastUpdated = $lastUpdated;

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
}
