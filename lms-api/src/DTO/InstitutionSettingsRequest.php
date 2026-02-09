<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class InstitutionSettingsRequest
{
    #[Assert\Url(requireTld: false)]
    public ?string $logo_url = null;

    public ?string $primary_color = null;
}
