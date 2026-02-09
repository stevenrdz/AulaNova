<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class QuestionCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $quiz_id;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['SINGLE', 'TEXT'])]
    public string $type;

    #[Assert\NotBlank]
    public string $prompt;

    public ?array $options = null;

    public ?string $correct_option = null;
}

