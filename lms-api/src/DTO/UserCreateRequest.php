<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    public string $password;

    #[Assert\NotBlank]
    public string $first_name;

    #[Assert\NotBlank]
    public string $last_name;

    #[Assert\Choice(choices: ['ROLE_ADMIN', 'ROLE_TEACHER', 'ROLE_STUDENT'])]
    public string $role = 'ROLE_STUDENT';
}
