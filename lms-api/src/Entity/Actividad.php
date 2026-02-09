<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'actividad')]
class Actividad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CursoVirtual::class)]
    #[ORM\JoinColumn(nullable: false)]
    private CursoVirtual $cursoVirtual;

    #[ORM\Column(type: 'string', length: 20)]
    private string $type;

    #[ORM\Column(type: 'string', length: 200)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $youtubeUrl = null;

    #[ORM\ManyToOne(targetEntity: FileObject::class)]
    private ?FileObject $file = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isGraded = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dueAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(CursoVirtual $cursoVirtual, string $type, string $title)
    {
        $this->cursoVirtual = $cursoVirtual;
        $this->type = $type;
        $this->title = $title;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCursoVirtual(): CursoVirtual
    {
        return $this->cursoVirtual;
    }

    public function setCursoVirtual(CursoVirtual $cursoVirtual): self
    {
        $this->cursoVirtual = $cursoVirtual;

        return $this;
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getYoutubeUrl(): ?string
    {
        return $this->youtubeUrl;
    }

    public function setYoutubeUrl(?string $youtubeUrl): self
    {
        $this->youtubeUrl = $youtubeUrl;

        return $this;
    }

    public function getFile(): ?FileObject
    {
        return $this->file;
    }

    public function setFile(?FileObject $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function isGraded(): bool
    {
        return $this->isGraded;
    }

    public function setIsGraded(bool $isGraded): self
    {
        $this->isGraded = $isGraded;

        return $this;
    }

    public function getDueAt(): ?\DateTimeImmutable
    {
        return $this->dueAt;
    }

    public function setDueAt(?\DateTimeImmutable $dueAt): self
    {
        $this->dueAt = $dueAt;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}

