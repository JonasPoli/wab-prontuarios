<?php

namespace App\Entity;

use App\Repository\ClientProjectHistoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientProjectHistoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ClientProjectHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'clientProjectHistories')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ClientProject $clientProject = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $occurredAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $transcript = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $audioFilename = null;

    /**
     * @var Collection<int, ClientProjectHistoryAttached>
     */
    #[ORM\OneToMany(
        targetEntity: ClientProjectHistoryAttached::class,
        mappedBy: 'clientProjectHistory',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $clientProjectHistoryAttacheds;

    public function __construct()
    {
        $this->clientProjectHistoryAttacheds = new ArrayCollection();
    }

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * @return Collection<int, ClientProjectHistoryAttached>
     */
    public function getClientProjectHistoryAttacheds(): Collection
    {
        return $this->clientProjectHistoryAttacheds;
    }

    public function addClientProjectHistoryAttached(ClientProjectHistoryAttached $clientProjectHistoryAttached): static
    {
        if (!$this->clientProjectHistoryAttacheds->contains($clientProjectHistoryAttached)) {
            $this->clientProjectHistoryAttacheds->add($clientProjectHistoryAttached);
            $clientProjectHistoryAttached->setClientProjectHistory($this);
        }

        return $this;
    }

    public function removeClientProjectHistoryAttached(ClientProjectHistoryAttached $clientProjectHistoryAttached): static
    {
        if ($this->clientProjectHistoryAttacheds->removeElement($clientProjectHistoryAttached)) {
            if ($clientProjectHistoryAttached->getClientProjectHistory() === $this) {
                $clientProjectHistoryAttached->setClientProjectHistory(null);
            }
        }

        return $this;
    }
}