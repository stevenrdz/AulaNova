<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class FileCompleteRequest
{
    #[Assert\NotBlank]
    public string $key;

    #[Assert\NotBlank]
    public string $bucket;

    #[Assert\NotBlank]
    public string $original_name;

    #[Assert\NotBlank]
    public string $mime_type;

    #[Assert\Positive]
    public int $size;
}
