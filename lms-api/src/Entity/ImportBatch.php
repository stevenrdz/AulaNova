<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'import_batch')]
class ImportBatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $type;

    #[ORM\Column(type: 'string', length: 30)]
    private string $status = 'pending';

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(targetEntity: FileObject::class)]
    private ?FileObject $resultFile = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $totalRows = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $successCount = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $errorCount = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $type)
    {
        $this->type = $type;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $user): self
    {
        $this->createdBy = $user;

        return $this;
    }

    public function getResultFile(): ?FileObject
    {
        return $this->resultFile;
    }

    public function setResultFile(?FileObject $file): self
    {
        $this->resultFile = $file;

        return $this;
    }

    public function getTotalRows(): ?int
    {
        return $this->totalRows;
    }

    public function setTotalRows(?int $totalRows): self
    {
        $this->totalRows = $totalRows;

        return $this;
    }

    public function getSuccessCount(): ?int
    {
        return $this->successCount;
    }

    public function setSuccessCount(?int $successCount): self
    {
        $this->successCount = $successCount;

        return $this;
    }

    public function getErrorCount(): ?int
    {
        return $this->errorCount;
    }

    public function setErrorCount(?int $errorCount): self
    {
        $this->errorCount = $errorCount;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
