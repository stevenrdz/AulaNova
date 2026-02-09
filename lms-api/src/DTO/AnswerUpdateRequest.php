<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AnswerUpdateRequest
{
    #[Assert\Positive]
    public ?int $attempt_id = null;

    #[Assert\Positive]
    public ?int $question_id = null;

    public ?string $answer_text = null;

    #[Assert\Type('bool')]
    public ?bool $is_correct = null;
}

