<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AsignaturaUpdateRequest
{
    #[Assert\Length(max: 120)]
    public ?string $name = null;

    #[Assert\Type('bool')]
    public ?bool $is_active = null;
}

