<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PeriodoUpdateRequest
{
    #[Assert\Length(max: 120)]
    public ?string $name = null;

    #[Assert\Date]
    public ?string $start_date = null;

    #[Assert\Date]
    public ?string $end_date = null;
}

