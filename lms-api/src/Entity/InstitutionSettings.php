<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'institution_settings')]
class InstitutionSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $logoUrl = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $primaryColor = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): self
    {
        $this->logoUrl = $logoUrl;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(?string $primaryColor): self
    {
        $this->primaryColor = $primaryColor;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
