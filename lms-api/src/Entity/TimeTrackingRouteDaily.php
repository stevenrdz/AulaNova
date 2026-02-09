<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'time_tracking_route_daily')]
class TimeTrackingRouteDaily
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Curso::class)]
    private ?Curso $curso = null;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $day;

    #[ORM\Column(type: 'string', length: 255)]
    private string $route;

    #[ORM\Column(type: 'integer')]
    private int $seconds = 0;

    public function __construct(User $user, \DateTimeImmutable $day, string $route)
    {
        $this->user = $user;
        $this->day = $day;
        $this->route = $route;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCurso(): ?Curso
    {
        return $this->curso;
    }

    public function setCurso(?Curso $curso): self
    {
        $this->curso = $curso;

        return $this;
    }

    public function getDay(): \DateTimeImmutable
    {
        return $this->day;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function incrementSeconds(int $amount): void
    {
        $this->seconds += $amount;
    }

    public function getSeconds(): int
    {
        return $this->seconds;
    }
}
