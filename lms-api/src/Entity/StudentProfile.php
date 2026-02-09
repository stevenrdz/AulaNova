<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'students_profile')]
class StudentProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $documentNumber = null;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    private ?string $phone = null;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
