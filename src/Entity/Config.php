<?php /** @noinspection UnknownInspectionInspection */

namespace Gupalo\ConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gupalo\ConfigBundle\Repository\ConfigRepository;

#[ORM\Entity(repositoryClass: ConfigRepository::class)]
#[ORM\Table(name: 'config')]
class Config
{
    /** @noinspection PhpPropertyOnlyWrittenInspection */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, unique: true)]
    private ?string $name = '';

    #[ORM\Column(name: 'value', type: 'text', nullable: true)]
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
        $this->name = mb_substr($name ?? '', 0, 255);

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
