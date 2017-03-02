<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Misc\GridItem;
use UJM\ExoBundle\Entity\Misc\GridOdd;
use UJM\ExoBundle\Entity\Misc\GridRow;
use UJM\ExoBundle\Library\Model\PenaltyTrait;
use UJM\ExoBundle\Library\Model\ShuffleTrait;

/**
 * A pair question.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_question_pair")
 */
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
     * @ORM\ManyToMany(targetEntity="UJM\ExoBundle\Entity\Misc\GridItem", cascade={"all"})
     * @ORM\JoinTable(
     *     name="ujm_question_pair_items",
     *     joinColumns={@ORM\JoinColumn(name="question_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id", unique=true)}
     * )
     *
     * @var ArrayCollection
     */
    private $items;

    /**
     * @ORM\OneToMany(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\GridRow",
     *     mappedBy="question",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     *
     * @var ArrayCollection
     */
    private $rows;

    /**
     * @ORM\OneToMany(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\GridOdd",
     *     mappedBy="question",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     *
     * @var ArrayCollection
     */
    private $oddItems;

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
     * @return ArrayCollection
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
     *
     * @param GridItem $gridItem
     */
    public function addItem(GridItem $gridItem)
    {
        if (!$this->items->contains($gridItem)) {
            $this->items->add($gridItem);
        }
    }

    /**
     * Remove item.
     *
     * @param GridItem $gridItem
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
     * @return ArrayCollection
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Add row.
     *
     * @param GridRow $row
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
     *
     * @param GridRow $row
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
     * @return ArrayCollection
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
     *
     * @param GridOdd $gridOdd
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
     *
     * @param GridOdd $gridOdd
     */
    public function removeOddItem(GridOdd $gridOdd)
    {
        if ($this->oddItems->contains($gridOdd)) {
            $this->oddItems->removeElement($gridOdd);
        }
    }
}
