<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class FilePresignRequest
{
    #[Assert\NotBlank]
    public string $filename;

    #[Assert\NotBlank]
    public string $mime_type;

    #[Assert\Positive]
    public int $size;
}
