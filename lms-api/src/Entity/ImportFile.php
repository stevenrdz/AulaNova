<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'import_files')]
class ImportFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ImportBatch::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ImportBatch $batch;

    #[ORM\ManyToOne(targetEntity: FileObject::class)]
    #[ORM\JoinColumn(nullable: false)]
    private FileObject $file;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(ImportBatch $batch, FileObject $file)
    {
        $this->batch = $batch;
        $this->file = $file;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBatch(): ImportBatch
    {
        return $this->batch;
    }

    public function getFile(): FileObject
    {
        return $this->file;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
