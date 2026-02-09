<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'import_row_error')]
class ImportRowError
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ImportBatch::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ImportBatch $batch;

    #[ORM\Column(type: 'integer')]
    private int $rowNumber;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $rawData = null;

    public function __construct(ImportBatch $batch, int $rowNumber, string $message)
    {
        $this->batch = $batch;
        $this->rowNumber = $rowNumber;
        $this->message = $message;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBatch(): ImportBatch
    {
        return $this->batch;
    }

    public function getRowNumber(): int
    {
        return $this->rowNumber;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    public function setRawData(?array $rawData): self
    {
        $this->rawData = $rawData;

        return $this;
    }
}
