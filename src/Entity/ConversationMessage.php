<?php

namespace App\Entity;

use App\Repository\ConversationMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationMessageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ConversationMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Qual conversa esta mensagem pertence
    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Conversation $conversation = null;

    // Quem enviou: 'user' (você) ou 'assistant' (GPT)
    #[ORM\Column(length: 20)]
    private ?string $role = null; // 'user' ou 'assistant'

    // O conteúdo da mensagem
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    // Quantos tokens esta mensagem consumiu (para controle de custos)
    #[ORM\Column(nullable: true)]
    private ?int $tokensUsed = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int { return $this->id; }

    public function getConversation(): ?Conversation { return $this->conversation; }
    public function setConversation(?Conversation $conversation): static { $this->conversation = $conversation; return $this; }

    public function getRole(): ?string { return $this->role; }
    public function setRole(string $role): static { $this->role = $role; return $this; }

    public function getContent(): ?string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }

    public function getTokensUsed(): ?int { return $this->tokensUsed; }
    public function setTokensUsed(?int $tokensUsed): static { $this->tokensUsed = $tokensUsed; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}