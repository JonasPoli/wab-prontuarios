<?php

namespace App\Entity;

use App\Repository\ClientProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientProjectRepository::class)]
class ClientProject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'clientProjects')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Client $client = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $fullDescription = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateEnd = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateStart = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $obs = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(
        targetEntity: ClientProjectHistory::class,
        mappedBy: 'clientProject',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $clientProjectHistories;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoFilename = null;

    #[ORM\OneToMany(
        targetEntity: ClientProjectAttached::class,
        mappedBy: 'projeto',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $clientProjectAttacheds;

    public function __construct()
    {
        $this->clientProjectHistories = new ArrayCollection();
        $this->clientProjectAttacheds = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getFullDescription(): ?string
    {
        return $this->fullDescription;
    }

    public function setFullDescription(?string $fullDescription): static
    {
        $this->fullDescription = $fullDescription;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?\DateTimeInterface $dateEnd): static
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(?\DateTimeInterface $dateStart): static
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getObs(): ?string
    {
        return $this->obs;
    }

    public function setObs(?string $obs): static
    {
        $this->obs = $obs;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, ClientProjectHistory>
     */
    public function getClientProjectHistories(): Collection
    {
        return $this->clientProjectHistories;
    }

    public function addClientProjectHistory(ClientProjectHistory $clientProjectHistory): static
    {
        if (!$this->clientProjectHistories->contains($clientProjectHistory)) {
            $this->clientProjectHistories->add($clientProjectHistory);
            $clientProjectHistory->setClientProject($this);
        }

        return $this;
    }

    public function removeClientProjectHistory(ClientProjectHistory $clientProjectHistory): static
    {
        if ($this->clientProjectHistories->removeElement($clientProjectHistory)) {
            if ($clientProjectHistory->getClientProject() === $this) {
                $clientProjectHistory->setClientProject(null);
            }
        }

        return $this;
    }

    public function getLogoFilename(): ?string
    {
        return $this->logoFilename;
    }

    public function setLogoFilename(?string $logoFilename): static
    {
        $this->logoFilename = $logoFilename;

        return $this;
    }

    /**
     * @return Collection<int, ClientProjectAttached>
     */
    public function getClientProjectAttacheds(): Collection
    {
        return $this->clientProjectAttacheds;
    }

    public function addClientProjectAttached(ClientProjectAttached $clientProjectAttached): static
    {
        if (!$this->clientProjectAttacheds->contains($clientProjectAttached)) {
            $this->clientProjectAttacheds->add($clientProjectAttached);
            $clientProjectAttached->setProjeto($this);
        }

        return $this;
    }

    public function removeClientProjectAttached(ClientProjectAttached $clientProjectAttached): static
    {
        if ($this->clientProjectAttacheds->removeElement($clientProjectAttached)) {
            if ($clientProjectAttached->getProjeto() === $this) {
                $clientProjectAttached->setProjeto(null);
            }
        }

        return $this;
    }
}