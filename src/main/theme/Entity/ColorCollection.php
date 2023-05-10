<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Name;
use Doctrine\ORM\Mapping as ORM;

/**
 * ColorCollection.
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_color_collection")
 */
class ColorCollection
{
    use Id;
    use Uuid;
    use Name;

    /**
     * @ORM\Column(type="json")
     */
    private array $colors = [];

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getColors(): array
    {
        return $this->colors;
    }

    public function setColors(array $colors): self
    {
        $this->colors = $colors;

        return $this;
    }

}
