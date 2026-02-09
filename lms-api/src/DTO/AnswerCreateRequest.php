<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AnswerCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $attempt_id;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $question_id;

    public ?string $answer_text = null;

    #[Assert\Type('bool')]
    public ?bool $is_correct = null;
}

