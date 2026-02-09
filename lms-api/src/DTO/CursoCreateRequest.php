<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CursoCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    public string $name;

    #[Assert\PositiveOrZero]
    public ?int $capacity = null;

    #[Assert\Date]
    public ?string $start_date = null;

    #[Assert\Date]
    public ?string $end_date = null;

    #[Assert\Positive]
    public ?int $periodo_id = null;

    #[Assert\Positive]
    public ?int $teacher_id = null;

    #[Assert\Positive]
    public ?int $sede_jornada_id = null;

    #[Assert\Positive]
    public ?int $carrera_id = null;

    #[Assert\Positive]
    public ?int $asignatura_id = null;
}

