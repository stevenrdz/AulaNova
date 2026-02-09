<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'audit_log')]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $action;

    #[ORM\Column(type: 'string', length: 100)]
    private string $entityType;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $entityId = null;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $action, string $entityType, ?string $entityId = null)
    {
        $this->action = $action;
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }
}
