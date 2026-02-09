<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class QuizCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $curso_virtual_id;

    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    public string $title;

    public ?string $description = null;

    #[Assert\DateTime]
    public ?string $start_at = null;

    #[Assert\DateTime]
    public ?string $end_at = null;

    #[Assert\Positive]
    public ?int $time_limit_minutes = null;
}

