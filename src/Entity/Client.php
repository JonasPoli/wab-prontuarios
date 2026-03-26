<?php

namespace App\Entity;

use App\Enum\ClientStatus;
use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fantasyName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $document = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mail = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone1 = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone2 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $obs = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoFilename = null;

    #[ORM\OneToMany(
        targetEntity: ClientProject::class,
        mappedBy: 'client',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $clientProjects;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    public function __construct()
    {
        $this->clientProjects = new ArrayCollection();
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

    public function getFantasyName(): ?string
    {
        return $this->fantasyName;
    }

    public function setFantasyName(?string $fantasyName): static
    {
        $this->fantasyName = $fantasyName;

        return $this;
    }

    public function getDocument(): ?string
    {
        return $this->document;
    }

    public function setDocument(?string $document): static
    {
        $this->document = $document;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getPhone1(): ?string
    {
        return $this->phone1;
    }

    public function setPhone1(?string $phone1): static
    {
        $this->phone1 = $phone1;

        return $this;
    }

    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    public function setPhone2(?string $phone2): static
    {
        $this->phone2 = $phone2;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getStatusEnum(): ?ClientStatus
    {
        return $this->status !== null ? ClientStatus::from($this->status) : null;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function setStatusEnum(?ClientStatus $status): static
    {
        $this->status = $status?->value;

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
     * @return Collection<int, ClientProject>
     */
    public function getClientProjects(): Collection
    {
        return $this->clientProjects;
    }

    public function addClientProject(ClientProject $clientProject): static
    {
        if (!$this->clientProjects->contains($clientProject)) {
            $this->clientProjects->add($clientProject);
            $clientProject->setClient($this);
        }

        return $this;
    }

    public function removeClientProject(ClientProject $clientProject): static
    {
        if ($this->clientProjects->removeElement($clientProject)) {
            if ($clientProject->getClient() === $this) {
                $clientProject->setClient(null);
            }
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }
}