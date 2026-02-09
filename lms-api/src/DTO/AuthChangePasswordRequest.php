<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AuthChangePasswordRequest
{
    #[Assert\NotBlank]
    public string $current_password;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    public string $new_password;
}
