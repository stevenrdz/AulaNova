<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AttemptCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $quiz_id;
}

