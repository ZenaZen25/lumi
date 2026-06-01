<?php

// namespace App\Entity;

// use App\Repository\MessageRepository;
// use Doctrine\DBAL\Types\Types;
// use Doctrine\ORM\Mapping as ORM;

// #[ORM\Entity(repositoryClass: MessageRepository::class)]
// class Message
// {
//     #[ORM\Id]
//     #[ORM\GeneratedValue]
//     #[ORM\Column]
//     private ?int $id = null;

//     #[ORM\Column(length: 10)]
//     private ?string $role = null;

//     #[ORM\Column(type: Types::TEXT)]
//     private ?string $content = null;

//     #[ORM\Column]
//     private ?\DateTime $createdAt = null;

//     #[ORM\ManyToOne(inversedBy: 'messages')]
//     #[ORM\JoinColumn(nullable: false)]
//     private ?Conversation $conversation = null;

//     public function getId(): ?int
//     {
//         return $this->id;
//     }

//     public function getRole(): ?string
//     {
//         return $this->role;
//     }

//     public function setRole(string $role): static
//     {
//         $this->role = $role;

//         return $this;
//     }

//     public function getContent(): ?string
//     {
//         return $this->content;
//     }

//     public function setContent(string $content): static
//     {
//         $this->content = $content;

//         return $this;
//     }

//     public function getCreatedAt(): ?\DateTime
//     {
//         return $this->createdAt;
//     }

//     public function setCreatedAt(\DateTime $createdAt): static
//     {
//         $this->createdAt = $createdAt;

//         return $this;
//     }

//     public function getConversation(): ?Conversation
//     {
//         return $this->conversation;
//     }

//     public function setConversation(?Conversation $conversation): static
//     {
//         $this->conversation = $conversation;

//         return $this;
//     }
// }

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Conversation $conversation = null;

    #[ORM\Column(length: 10)]
    private ?string $sender = null; // 'user' ou 'echo'

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isAlert = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getConversation(): ?Conversation { return $this->conversation; }
    public function setConversation(?Conversation $conversation): static { $this->conversation = $conversation; return $this; }

    public function getSender(): ?string { return $this->sender; }
    public function setSender(string $sender): static { $this->sender = $sender; return $this; }

    public function getContent(): ?string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function isAlert(): ?bool { return $this->isAlert; }
    public function setIsAlert(?bool $isAlert): static { $this->isAlert = $isAlert; return $this; }
}