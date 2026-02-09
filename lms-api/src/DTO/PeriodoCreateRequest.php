<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PeriodoCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $name;

    #[Assert\Date]
    public ?string $start_date = null;

    #[Assert\Date]
    public ?string $end_date = null;
}

