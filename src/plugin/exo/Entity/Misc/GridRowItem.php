<?php

namespace UJM\ExoBundle\Entity\Misc;

use Claroline\AppBundle\Entity\Meta\Order;
use Doctrine\ORM\Mapping as ORM;

/**
 * GridRowItem.
 *
 *
 */
#[ORM\Table('ujm_grid_row_item')]
#[ORM\Entity]
class GridRowItem
{
    use Order;

    /**
     *
     *
     *
     * @var GridRow
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: GridRow::class, inversedBy: 'rowItems')]
    private $row;

    /**
     *
     *
     *
     * @var GridItem
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: GridItem::class, cascade: ['persist'])]
    private $item;

    /**
     * Get row.
     *
     * @return GridRow
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * Set row.
     */
    public function setRow(GridRow $row)
    {
        $this->row = $row;
    }

    /**
     * Get item.
     *
     * @return GridItem
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set item.
     */
    public function setItem(GridItem $item)
    {
        $this->item = $item;
    }
}
