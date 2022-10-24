<?php

namespace App\Entity;

use App\Repository\UserSessionRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\AST\Functions\CurrentTimestampFunction;

#[ORM\Entity(repositoryClass: UserSessionRepository::class)]
class UserSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * A Supprimer - remplacé par $jwt
     */
    #[ORM\OneToOne(inversedBy: 'userSession', targetEntity: Token::class, cascade: ['persist', 'remove'])]
    //#[ORM\JoinColumn(nullable: false)]
    #[ORM\JoinColumn(nullable: true)]
    private $token;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'] )]
    private $lasted_at;

    #[ORM\Column(type: 'datetime', options: ['default' => '1000-01-01 00:00:00'])]
    private $finished_at;

    #[ORM\Column(type: 'datetime', options: ['default' => '1000-01-01 00:00:00'])]
    private $created_at;

    #[ORM\Column(type: 'string', length: 45)]
    private $user_ip;

    #[ORM\Column(type: 'string', length: 255)]
    private $user_agent;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userSessions')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'text')]
    private $jwt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }

    public function setToken(Token $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getLastedAt(): ?\DateTimeInterface
    {
        return $this->lasted_at;
    }

    public function setLastedAt(?\DateTimeInterface $lasted_at): self
    {
        $this->lasted_at = $lasted_at;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finished_at;
    }

    public function setFinishedAt(?\DateTimeInterface $finished_at): self
    {
        $this->finished_at = $finished_at;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUserIp(): ?string
    {
        return $this->user_ip;
    }

    public function setUserIp(string $user_ip): self
    {
        $this->user_ip = $user_ip;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->user_agent;
    }

    public function setUserAgent(string $user_agent): self
    {
        $this->user_agent = $user_agent;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getJwt(): ?string
    {
        return $this->jwt;
    }

    public function setJwt(string $jwt): self
    {
        $this->jwt = $jwt;

        return $this;
    }
}
