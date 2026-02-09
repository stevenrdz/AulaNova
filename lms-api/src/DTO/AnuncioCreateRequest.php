<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AnuncioCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $curso_virtual_id;

    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    public string $title;

    #[Assert\NotBlank]
    public string $content;
}

