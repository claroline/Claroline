<?php

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Library\Model\ContentTrait;

/**
 * GridItem.
 */
#[ORM\Table(name: 'ujm_grid_item')]
#[ORM\Entity]
class GridItem
{
    use Id;
    use ContentTrait;
    use Uuid;

    /**
     * X coordinate of the item in the grid.
     *
     *
     * @var int
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private $coordsX = null;

    /**
     * Y coordinate of the item in the grid.
     *
     *
     * @var int
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private $coordsY = null;

    /**
     * GridItem constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get X coordinate.
     *
     * @return int
     */
    public function getCoordsX()
    {
        return $this->coordsX;
    }

    /**
     * Set X coordinate.
     *
     * @param int $coordsX
     */
    public function setCoordsX($coordsX)
    {
        $this->coordsX = $coordsX;
    }

    /**
     * Get Y coordinate.
     *
     * @return int
     */
    public function getCoordsY()
    {
        return $this->coordsY;
    }

    /**
     * Set Y coordinate.
     *
     * @param $coordsY
     */
    public function setCoordsY($coordsY)
    {
        $this->coordsY = $coordsY;
    }

    /**
     * Get coordinates.
     *
     * @return array
     */
    public function getCoords()
    {
        return (is_int($this->coordsX) || is_int($this->coordsY)) ?
            [$this->coordsX, $this->coordsY] : null;
    }
}
