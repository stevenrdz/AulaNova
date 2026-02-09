<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ImportUsersRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $file_id;
}
