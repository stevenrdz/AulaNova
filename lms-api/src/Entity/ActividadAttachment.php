<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'actividad_attachment')]
class ActividadAttachment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Actividad::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Actividad $actividad;

    #[ORM\ManyToOne(targetEntity: FileObject::class)]
    #[ORM\JoinColumn(nullable: false)]
    private FileObject $file;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(Actividad $actividad, FileObject $file)
    {
        $this->actividad = $actividad;
        $this->file = $file;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActividad(): Actividad
    {
        return $this->actividad;
    }

    public function getFile(): FileObject
    {
        return $this->file;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}

