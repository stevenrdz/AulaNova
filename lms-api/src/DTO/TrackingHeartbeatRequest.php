<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class TrackingHeartbeatRequest
{
    #[Assert\NotBlank]
    public string $route;

    public ?int $course_id = null;

    #[Assert\NotBlank]
    public int $timestamp;
}
