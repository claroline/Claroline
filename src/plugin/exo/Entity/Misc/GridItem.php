<?php

namespace UJM\ExoBundle\Entity\Misc;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Library\Model\ContentTrait;

/**
 * GridItem.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_grid_item")
 */
class GridItem
{
    use ContentTrait;
    use Uuid;
    /**
     * Unique identifier of the item.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * X coordinate of the item in the grid.
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $coordsX = null;

    /**
     * Y coordinate of the item in the grid.
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $coordsY = null;

    /**
     * GridItem constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
