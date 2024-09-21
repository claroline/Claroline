<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Misc\GridItem;
use UJM\ExoBundle\Entity\Misc\GridOdd;
use UJM\ExoBundle\Entity\Misc\GridRow;
use UJM\ExoBundle\Library\Model\PenaltyTrait;
use UJM\ExoBundle\Library\Model\ShuffleTrait;

/**
 * A pair question.
 */
#[ORM\Table(name: 'ujm_question_pair')]
#[ORM\Entity]
class PairQuestion extends AbstractItem
{
    use ShuffleTrait;
    /*
     * The penalty to apply to each wrong association
     */
    use PenaltyTrait;

    /**
     * List of available items for the question.
     *
     *
     * @var Collection<int, GridItem>
     */
    #[ORM\JoinTable(name: 'ujm_question_pair_items')]
    #[ORM\JoinColumn(name: 'question_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'item_id', referencedColumnName: 'id', unique: true)]
    #[ORM\ManyToMany(targetEntity: GridItem::class, cascade: ['all'])]
    private Collection $items;

    /**
     * @var Collection<int, GridRow>
     */
    #[ORM\OneToMany(targetEntity: GridRow::class, mappedBy: 'question', cascade: ['all'], orphanRemoval: true)]
    private Collection $rows;

    /**
     * @var Collection<int, GridOdd>
     */
    #[ORM\OneToMany(targetEntity: GridOdd::class, mappedBy: 'question', cascade: ['all'], orphanRemoval: true)]
    private Collection $oddItems;

    /**
     * PairQuestion constructor.
     */
    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->rows = new ArrayCollection();
        $this->oddItems = new ArrayCollection();
    }

    /**
     * Get items.
     *
     * @return GridItem[]|ArrayCollection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get an item by its uuid.
     *
     * @param $uuid
     *
     * @return GridItem|null
     */
    public function getItem($uuid)
    {
        $found = null;
        foreach ($this->items as $item) {
            if ($item->getUuid() === $uuid) {
                $found = $item;
                break;
            }
        }

        return $found;
    }

    /**
     * Add item.
     */
    public function addItem(GridItem $gridItem)
    {
        if (!$this->items->contains($gridItem)) {
            $this->items->add($gridItem);
        }
    }

    /**
     * Remove item.
     */
    public function removeItem(GridItem $gridItem)
    {
        if ($this->items->contains($gridItem)) {
            $this->items->removeElement($gridItem);
        }
    }

    /**
     * Get rows.
     *
     * @return GridRow[]|ArrayCollection
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Add row.
     */
    public function addRow(GridRow $row)
    {
        if (!$this->rows->contains($row)) {
            $this->rows->add($row);
            $row->setQuestion($this);
        }
    }

    /**
     * Remove row.
     */
    public function removeRow(GridRow $row)
    {
        if ($this->rows->contains($row)) {
            $this->rows->removeElement($row);
        }
    }

    /**
     * Get odd items.
     *
     * @return GridOdd[]|ArrayCollection
     */
    public function getOddItems()
    {
        return $this->oddItems;
    }

    /**
     * Get an odd item by its uuid.
     *
     * @param $uuid
     *
     * @return GridOdd|null
     */
    public function getOddItem($uuid)
    {
        $found = null;
        foreach ($this->oddItems as $oddItem) {
            if ($oddItem->getItem()->getUuid() === $uuid) {
                $found = $oddItem;
                break;
            }
        }

        return $found;
    }

    /**
     * Add odd item.
     */
    public function addOddItem(GridOdd $gridOdd)
    {
        if (!$this->oddItems->contains($gridOdd)) {
            $this->oddItems->add($gridOdd);
            $gridOdd->setQuestion($this);
        }
    }

    /**
     * Remove odd item.
     */
    public function removeOddItem(GridOdd $gridOdd)
    {
        if ($this->oddItems->contains($gridOdd)) {
            $this->oddItems->removeElement($gridOdd);
        }
    }
}
