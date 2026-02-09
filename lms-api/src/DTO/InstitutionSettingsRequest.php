<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class InstitutionSettingsRequest
{
    #[Assert\AtLeastOneOf([
        new Assert\Url(requireTld: false),
        new Assert\Regex(pattern: '/^[A-Za-z0-9._\\/-]+$/'),
    ])]
    public ?string $logo_url = null;

    public ?string $primary_color = null;
}
