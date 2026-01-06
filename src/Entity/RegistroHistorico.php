<?php

namespace App\Entity;

use App\Repository\RegistroHistoricoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RegistroHistoricoRepository::class)]
#[ORM\Table(name: 'registro_historico')]
class RegistroHistorico
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'registrosHistorico')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'O projeto é obrigatório.')]
    private ?Projeto $projeto = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: 'A data do registro é obrigatória.')]
    private ?\DateTimeInterface $dataRegistro = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $tipoRegistro = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titulo = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'A descrição é obrigatória.')]
    private ?string $descricao = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $visivelParaCliente = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tags = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $usuarioAutor = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, RegistroHistoricoAnexo>
     */
    #[ORM\OneToMany(targetEntity: RegistroHistoricoAnexo::class, mappedBy: 'historicoAnexo')]
    private Collection $registroHistoricoAnexos;

    public function __construct()
    {
        $this->dataRegistro = new \DateTime();
        $this->createdAt = new \DateTimeImmutable();
        $this->registroHistoricoAnexos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    

    public function getProjeto(): ?Projeto
    {
        return $this->projeto;
    }

    public function setProjeto(?Projeto $projeto): static
    {
        $this->projeto = $projeto;
        return $this;
    }

    public function getDataRegistro(): ?\DateTimeInterface
    {
        return $this->dataRegistro;
    }

    public function setDataRegistro(\DateTimeInterface $dataRegistro): static
    {
        $this->dataRegistro = $dataRegistro;
        return $this;
    }

    public function getTipoRegistro(): ?string
    {
        return $this->tipoRegistro;
    }

    public function setTipoRegistro(?string $tipoRegistro): static
    {
        $this->tipoRegistro = $tipoRegistro;
        return $this;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(?string $titulo): static
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(string $descricao): static
    {
        $this->descricao = $descricao;
        return $this;
    }

    public function isVisivelParaCliente(): bool
    {
        return $this->visivelParaCliente;
    }

    public function setVisivelParaCliente(bool $visivelParaCliente): static
    {
        $this->visivelParaCliente = $visivelParaCliente;
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

    public function getUsuarioAutor(): ?User
    {
        return $this->usuarioAutor;
    }

    public function setUsuarioAutor(?User $usuarioAutor): static
    {
        $this->usuarioAutor = $usuarioAutor;
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
     * @return Collection<int, RegistroHistoricoAnexo>
     */
    public function getRegistroHistoricoAnexos(): Collection
    {
        return $this->registroHistoricoAnexos;
    }

    public function addRegistroHistoricoAnexo(RegistroHistoricoAnexo $registroHistoricoAnexo): static
    {
        if (!$this->registroHistoricoAnexos->contains($registroHistoricoAnexo)) {
            $this->registroHistoricoAnexos->add($registroHistoricoAnexo);
            $registroHistoricoAnexo->setHistoricoAnexo($this);
        }

        return $this;
    }

    public function removeRegistroHistoricoAnexo(RegistroHistoricoAnexo $registroHistoricoAnexo): static
    {
        if ($this->registroHistoricoAnexos->removeElement($registroHistoricoAnexo)) {
            // set the owning side to null (unless already changed)
            if ($registroHistoricoAnexo->getHistoricoAnexo() === $this) {
                $registroHistoricoAnexo->setHistoricoAnexo(null);
            }
        }

        return $this;
    }

    



}
