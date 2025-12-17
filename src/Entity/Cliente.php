<?php

namespace App\Entity;

use App\Repository\ClienteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClienteRepository::class)]
class Cliente
{

  

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['pessoa_fisica', 'pessoa_juridica'])]
    private ?string $tipo = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $nome = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apelidoFantasia = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $documento = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telefonePrincipal = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telefoneSecundario = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observacoes = null;

    #[ORM\Column]
    private ?bool $status = true;

    #[ORM\OneToMany(mappedBy: 'cliente', targetEntity: Projeto::class)]
    private Collection $projetos;

    public function __construct()
    {
        $this->projetos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;
        return $this;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): static
    {
        $this->nome = $nome;
        return $this;
    }

    public function getApelidoFantasia(): ?string
    {
        return $this->apelidoFantasia;
    }

    public function setApelidoFantasia(?string $apelidoFantasia): static
    {
        $this->apelidoFantasia = $apelidoFantasia;
        return $this;
    }

    public function getDocumento(): ?string
    {
        return $this->documento;
    }

    public function setDocumento(?string $documento): static
    {
        $this->documento = $documento;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getTelefonePrincipal(): ?string
    {
        return $this->telefonePrincipal;
    }

    public function setTelefonePrincipal(?string $telefonePrincipal): static
    {
        $this->telefonePrincipal = $telefonePrincipal;
        return $this;
    }

    public function getTelefoneSecundario(): ?string
    {
        return $this->telefoneSecundario;
    }

    public function setTelefoneSecundario(?string $telefoneSecundario): static
    {
        $this->telefoneSecundario = $telefoneSecundario;
        return $this;
    }

    public function getObservacoes(): ?string
    {
        return $this->observacoes;
    }

    public function setObservacoes(?string $observacoes): static
    {
        $this->observacoes = $observacoes;
        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getProjetos(): Collection
    {
        return $this->projetos;
    }

    public function __toString(): string
    {
        return $this->nome;
    }
}
