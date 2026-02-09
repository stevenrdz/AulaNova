<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ActividadEntregaGradeRequest
{
    #[Assert\Range(min: 0, max: 100)]
    public ?float $grade = null;

    #[Assert\Length(max: 5000)]
    public ?string $feedback = null;

    #[Assert\Length(max: 20)]
    public ?string $status = null;
}
