<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AnuncioUpdateRequest
{
    #[Assert\Positive]
    public ?int $curso_virtual_id = null;

    #[Assert\Length(max: 200)]
    public ?string $title = null;

    public ?string $content = null;
}

