<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function getItem(string $uuid): ?GridItem
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
    public function addItem(GridItem $gridItem): void
    {
        if (!$this->items->contains($gridItem)) {
            $this->items->add($gridItem);
        }
    }

    /**
     * Remove item.
     */
    public function removeItem(GridItem $gridItem): void
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
    public function getRows(): Collection
    {
        return $this->rows;
    }

    /**
     * Add row.
     */
    public function addRow(GridRow $row): void
    {
        if (!$this->rows->contains($row)) {
            $this->rows->add($row);
            $row->setQuestion($this);
        }
    }

    /**
     * Remove row.
     */
    public function removeRow(GridRow $row): void
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
    public function getOddItems(): Collection
    {
        return $this->oddItems;
    }

    public function getOddItem(string $uuid): ?GridOdd
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

    public function addOddItem(GridOdd $gridOdd): void
    {
        if (!$this->oddItems->contains($gridOdd)) {
            $this->oddItems->add($gridOdd);
            $gridOdd->setQuestion($this);
        }
    }

    public function removeOddItem(GridOdd $gridOdd): void
    {
        if ($this->oddItems->contains($gridOdd)) {
            $this->oddItems->removeElement($gridOdd);
        }
    }
}
