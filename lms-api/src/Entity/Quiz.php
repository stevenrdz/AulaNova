<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'quiz')]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CursoVirtual::class)]
    #[ORM\JoinColumn(nullable: false)]
    private CursoVirtual $cursoVirtual;

    #[ORM\Column(type: 'string', length: 200)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $endAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $timeLimitMinutes = null;

    public function __construct(CursoVirtual $cursoVirtual, string $title)
    {
        $this->cursoVirtual = $cursoVirtual;
        $this->title = $title;
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeImmutable $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeImmutable $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getTimeLimitMinutes(): ?int
    {
        return $this->timeLimitMinutes;
    }

    public function setTimeLimitMinutes(?int $timeLimitMinutes): self
    {
        $this->timeLimitMinutes = $timeLimitMinutes;

        return $this;
    }
}

