<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserUpdateRequest
{
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\Length(min: 8)]
    public ?string $password = null;

    public ?string $first_name = null;

    public ?string $last_name = null;

    #[Assert\Choice(choices: ['ROLE_ADMIN', 'ROLE_TEACHER', 'ROLE_STUDENT'])]
    public ?string $role = null;
}
