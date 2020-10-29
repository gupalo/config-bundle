<?php

namespace Gupalo\ConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Gupalo\ConfigBundle\Repository\ConfigRepository")
 * @ORM\Table(name="config")
 */
class Config
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true, name="name")
     */
    private ?string $name = '';

    /**
     * @ORM\Column(type="text", nullable=true, name="value")
     */
    private ?string $value = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name ?? '';
    }

    public function setName(?string $name): self
    {
        $this->name = $name ?? '';

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
