<?php

namespace App\Entity;

use App\Repository\ClientProjectHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientProjectHistoryRepository::class)]
class ClientProjectHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'clientProjectHistories')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ClientProject $clientProject = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $occurredAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $transcript = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $audioFilename = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientProject(): ?ClientProject
    {
        return $this->clientProject;
    }

    public function setClientProject(?ClientProject $clientProject): static
    {
        $this->clientProject = $clientProject;

        return $this;
    }

    public function getOccurredAt(): ?\DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function setOccurredAt(?\DateTimeImmutable $occurredAt): static
    {
        $this->occurredAt = $occurredAt;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }

    public function getTranscript(): ?string
    {
        return $this->transcript;
    }

    public function setTranscript(?string $transcript): static
    {
        $this->transcript = $transcript;

        return $this;
    }

    public function getAudioFilename(): ?string
    {
        return $this->audioFilename;
    }

    public function setAudioFilename(?string $audioFilename): static
    {
        $this->audioFilename = $audioFilename;

        return $this;
    }
}