<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CursoVirtualCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $curso_id;

    #[Assert\Length(max: 2000)]
    public ?string $description = null;
}

