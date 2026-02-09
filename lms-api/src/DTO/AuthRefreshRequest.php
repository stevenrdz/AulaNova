<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AuthRefreshRequest
{
    #[Assert\NotBlank]
    public string $refresh_token;
}
