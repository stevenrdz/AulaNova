<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AuthResetPasswordRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6, max: 6)]
    public string $otp;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    public string $new_password;
}
