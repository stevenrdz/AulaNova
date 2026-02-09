<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ActividadCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $curso_virtual_id;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['TEXT', 'FILE', 'VIDEO', 'TASK'])]
    public string $type;

    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    public string $title;

    public ?string $content = null;

    #[Assert\Url(requireTld: false)]
    public ?string $youtube_url = null;

    #[Assert\Positive]
    public ?int $file_id = null;

    #[Assert\Type('bool')]
    public ?bool $is_graded = null;

    #[Assert\DateTime]
    public ?string $due_at = null;

    #[Assert\All([
        new Assert\Positive()
    ])]
    public ?array $attachment_ids = null;
}

