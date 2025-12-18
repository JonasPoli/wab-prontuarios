<?php

namespace App\Entity;

use App\Repository\ProjetoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjetoRepository::class)]
class Projeto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'projetos')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'O cliente é obrigatório.')]
    private ?Cliente $cliente = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'O título do projeto é obrigatório.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'O título do projeto não pode ultrapassar 255 caracteres.'
    )]
    private ?string $titulo = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $codigoInterno = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $descricaoResumida = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descricaoDetalhada = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'O status do projeto é obrigatório.')]
    private ?string $status = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dataInicioPrevista = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dataFimPrevista = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dataInicioReal = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dataFimReal = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $responsavelInterno = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tags = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observacoes = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, RegistroHistorico>
     */
    #[ORM\OneToMany(
        mappedBy: 'projeto',
        targetEntity: RegistroHistorico::class,
        orphanRemoval: true
    )]
    private Collection $registrosHistorico;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->registrosHistorico = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCliente(): ?Cliente
    {
        return $this->cliente;
    }

    public function setCliente(?Cliente $cliente): static
    {
        $this->cliente = $cliente;
        return $this;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function getCodigoInterno(): ?string
    {
        return $this->codigoInterno;
    }

    public function setCodigoInterno(?string $codigoInterno): static
    {
        $this->codigoInterno = $codigoInterno;
        return $this;
    }

    public function getDescricaoResumida(): ?string
    {
        return $this->descricaoResumida;
    }

    public function setDescricaoResumida(?string $descricaoResumida): static
    {
        $this->descricaoResumida = $descricaoResumida;
        return $this;
    }

    public function getDescricaoDetalhada(): ?string
    {
        return $this->descricaoDetalhada;
    }

    public function setDescricaoDetalhada(?string $descricaoDetalhada): static
    {
        $this->descricaoDetalhada = $descricaoDetalhada;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getDataInicioPrevista(): ?\DateTimeInterface
    {
        return $this->dataInicioPrevista;
    }

    public function setDataInicioPrevista(?\DateTimeInterface $dataInicioPrevista): static
    {
        $this->dataInicioPrevista = $dataInicioPrevista;
        return $this;
    }

    public function getDataFimPrevista(): ?\DateTimeInterface
    {
        return $this->dataFimPrevista;
    }

    public function setDataFimPrevista(?\DateTimeInterface $dataFimPrevista): static
    {
        $this->dataFimPrevista = $dataFimPrevista;
        return $this;
    }

    public function getDataInicioReal(): ?\DateTimeInterface
    {
        return $this->dataInicioReal;
    }

    public function setDataInicioReal(?\DateTimeInterface $dataInicioReal): static
    {
        $this->dataInicioReal = $dataInicioReal;
        return $this;
    }

    public function getDataFimReal(): ?\DateTimeInterface
    {
        return $this->dataFimReal;
    }

    public function setDataFimReal(?\DateTimeInterface $dataFimReal): static
    {
        $this->dataFimReal = $dataFimReal;
        return $this;
    }

    public function getResponsavelInterno(): ?User
    {
        return $this->responsavelInterno;
    }

    public function setResponsavelInterno(?User $responsavelInterno): static
    {
        $this->responsavelInterno = $responsavelInterno;
        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): static
    {
        $this->tags = $tags;
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
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
     * @return Collection<int, RegistroHistorico>
     */
    public function getRegistrosHistorico(): Collection
    {
        return $this->registrosHistorico;
    }

    public function addRegistroHistorico(RegistroHistorico $registroHistorico): static
    {
        if (!$this->registrosHistorico->contains($registroHistorico)) {
            $this->registrosHistorico->add($registroHistorico);
            $registroHistorico->setProjeto($this);
        }

        return $this;
    }

    public function removeRegistroHistorico(RegistroHistorico $registroHistorico): static
    {
        if ($this->registrosHistorico->removeElement($registroHistorico)) {
            if ($registroHistorico->getProjeto() === $this) {
                $registroHistorico->setProjeto(null);
            }
        }

        return $this;
    }
}
