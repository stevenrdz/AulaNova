<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CursoVirtualUpdateRequest
{
    #[Assert\Positive]
    public ?int $curso_id = null;

    #[Assert\Length(max: 2000)]
    public ?string $description = null;
}

