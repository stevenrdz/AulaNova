<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ActividadUpdateRequest
{
    #[Assert\Positive]
    public ?int $curso_virtual_id = null;

    #[Assert\Choice(choices: ['TEXT', 'FILE', 'VIDEO', 'TASK'])]
    public ?string $type = null;

    #[Assert\Length(max: 200)]
    public ?string $title = null;

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

