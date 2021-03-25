<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Misc\OrderingItem;
use UJM\ExoBundle\Library\Model\PenaltyTrait;
use UJM\ExoBundle\Library\Options\Direction;

/**
 * An ordering question.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_question_ordering")
 */
class OrderingQuestion extends AbstractItem
{
    use PenaltyTrait;

    /**
     * The user will reorder items within container.
     *
     * @var string
     */
    const MODE_INSIDE = 'inside';

    /**
     * The user will reorder items in another container.
     *
     * @var string
     */
    const MODE_BESIDE = 'beside';

    /**
     * @ORM\OneToMany(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\OrderingItem",
     *     mappedBy="question",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"position" = "ASC"})
     *
     * @var OrderingItem[]|ArrayCollection
     */
    private $items;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $direction = Direction::VERTICAL;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $mode = self::MODE_INSIDE;

    /**
     * Constructs a new instance of Ordering question.
     */
    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * @param $mode
     */
    public function setMode($mode)
    {
        if (self::MODE_INSIDE === $mode || self::MODE_BESIDE === $mode) {
            $this->mode = $mode;
        }
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param $direction
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * Get items.
     *
     * @return OrderingItem[]|ArrayCollection
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
     * @return OrderingItem|null
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
    public function addItem(OrderingItem $item)
    {
        if (!$this->items->contains($item)) {
            $item->setQuestion($this);
            $this->items->add($item);
        }
    }

    /**
     * Remove item.
     */
    public function removeItem(OrderingItem $item)
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
        }
    }
}
