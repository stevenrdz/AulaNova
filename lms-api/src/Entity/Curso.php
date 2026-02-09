<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'curso')]
class Curso
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 150)]
    private string $name;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $capacity = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\ManyToOne(targetEntity: Periodo::class)]
    private ?Periodo $periodo = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $teacher = null;

    #[ORM\ManyToOne(targetEntity: SedeJornada::class)]
    private ?SedeJornada $sedeJornada = null;

    #[ORM\ManyToOne(targetEntity: Carrera::class)]
    private ?Carrera $carrera = null;

    #[ORM\ManyToOne(targetEntity: Asignatura::class)]
    private ?Asignatura $asignatura = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(?int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getPeriodo(): ?Periodo
    {
        return $this->periodo;
    }

    public function setPeriodo(?Periodo $periodo): self
    {
        $this->periodo = $periodo;

        return $this;
    }

    public function getTeacher(): ?User
    {
        return $this->teacher;
    }

    public function setTeacher(?User $teacher): self
    {
        $this->teacher = $teacher;

        return $this;
    }

    public function getSedeJornada(): ?SedeJornada
    {
        return $this->sedeJornada;
    }

    public function setSedeJornada(?SedeJornada $sedeJornada): self
    {
        $this->sedeJornada = $sedeJornada;

        return $this;
    }

    public function getCarrera(): ?Carrera
    {
        return $this->carrera;
    }

    public function setCarrera(?Carrera $carrera): self
    {
        $this->carrera = $carrera;

        return $this;
    }

    public function getAsignatura(): ?Asignatura
    {
        return $this->asignatura;
    }

    public function setAsignatura(?Asignatura $asignatura): self
    {
        $this->asignatura = $asignatura;

        return $this;
    }
}

