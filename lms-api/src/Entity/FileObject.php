<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'file_object')]
class FileObject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $objectKey;

    #[ORM\Column(type: 'string', length: 255)]
    private string $bucket;

    #[ORM\Column(type: 'string', length: 255)]
    private string $originalName;

    #[ORM\Column(type: 'string', length: 100)]
    private string $mimeType;

    #[ORM\Column(type: 'integer')]
    private int $size;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $createdBy = null;

    public function __construct(string $objectKey, string $bucket, string $originalName, string $mimeType, int $size)
    {
        $this->objectKey = $objectKey;
        $this->bucket = $bucket;
        $this->originalName = $originalName;
        $this->mimeType = $mimeType;
        $this->size = $size;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObjectKey(): string
    {
        return $this->objectKey;
    }

    public function getBucket(): string
    {
        return $this->bucket;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setCreatedBy(?User $user): self
    {
        $this->createdBy = $user;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }
}
