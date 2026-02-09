<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class QuestionUpdateRequest
{
    #[Assert\Positive]
    public ?int $quiz_id = null;

    #[Assert\Choice(choices: ['SINGLE', 'TEXT'])]
    public ?string $type = null;

    public ?string $prompt = null;

    public ?array $options = null;

    public ?string $correct_option = null;
}

