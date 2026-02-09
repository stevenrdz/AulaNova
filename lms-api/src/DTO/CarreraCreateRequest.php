<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CarreraCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $name;

    #[Assert\Type('bool')]
    public ?bool $is_active = null;
}

