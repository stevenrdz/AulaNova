<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ActividadEntregaCreateRequest
{
    #[Assert\Positive]
    public ?int $file_id = null;

    #[Assert\Length(max: 10000)]
    public ?string $content = null;
}
