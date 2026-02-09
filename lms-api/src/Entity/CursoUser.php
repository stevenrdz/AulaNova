<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'curso_users')]
class CursoUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Curso::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Curso $curso;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'string', length: 30)]
    private string $role = 'student';

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(Curso $curso, User $user, string $role = 'student')
    {
        $this->curso = $curso;
        $this->user = $user;
        $this->role = $role;
        $this->createdAt = new \DateTimeImmutable();
    }
}
