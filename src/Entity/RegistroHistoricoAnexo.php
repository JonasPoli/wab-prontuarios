<?php

namespace App\Entity;

use App\Repository\RegistroHistoricoAnexoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegistroHistoricoAnexoRepository::class)]
class RegistroHistoricoAnexo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $file = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'registroHistoricoAnexos')]
    private ?RegistroHistorico $historicoAnexo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getHistoricoAnexo(): ?RegistroHistorico
    {
        return $this->historicoAnexo;
    }

    public function setHistoricoAnexo(?RegistroHistorico $historicoAnexo): static
    {
        $this->historicoAnexo = $historicoAnexo;

        return $this;
    }
}
