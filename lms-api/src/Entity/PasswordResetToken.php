<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'password_reset_tokens')]
class PasswordResetToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'string', length: 128)]
    private string $otpHash;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $usedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(User $user, string $otpHash, \DateTimeImmutable $expiresAt)
    {
        $this->user = $user;
        $this->otpHash = $otpHash;
        $this->expiresAt = $expiresAt;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function isValid(string $otpHash): bool
    {
        return $this->usedAt === null && $this->expiresAt > new \DateTimeImmutable() && hash_equals($this->otpHash, $otpHash);
    }

    public function markUsed(): void
    {
        $this->usedAt = new \DateTimeImmutable();
    }
}
