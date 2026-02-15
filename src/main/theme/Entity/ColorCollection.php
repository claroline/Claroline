<?php

namespace Claroline\ThemeBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Name;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ColorCollection.
 */
#[ORM\Table(name: 'claro_color_collection')]
#[ORM\Entity]
class ColorCollection
{
    use Id;
    use Uuid;
    use Name;

    #[ORM\Column(type: Types::JSON)]
    private array $colors = [];

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getColors(): array
    {
        return array_values($this->colors);
    }

    public function setColors(array $colors): self
    {
        $this->colors = array_values($colors);

        return $this;
    }
}
