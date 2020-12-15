<?php

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\PairQuestion;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

/**
 * GridRow.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_grid_row")
 */
class GridRow implements AnswerPartInterface
{
    /**
     * Unique identifier of the row.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    use ScoreTrait;

    use FeedbackTrait;

    /**
     * If set to true the items order in answer must match the order set by the author.
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $ordered;

    /**
     * The list of items in the row.
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\Misc\GridRowItem", mappedBy="row", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"order" = "ASC"})
     */
    private $rowItems;

    /**
     * The parent question.
     *
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\ItemType\PairQuestion", inversedBy="rows")
     * @ORM\JoinColumn(name="pair_question_id", referencedColumnName="id")
     *
     * @var PairQuestion
     */
    private $question;

    /**
     * GridRow constructor.
     */
    public function __construct()
    {
        $this->rowItems = new ArrayCollection();
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
     * Is ordered ?
     *
     * @return bool
     */
    public function isOrdered()
    {
        return $this->ordered;
    }

    /**
     * Set ordered.
     *
     * @param $ordered
     */
    public function setOrdered($ordered)
    {
        $this->ordered = $ordered;
    }

    /**
     * Get items.
     *
     * @return GridItem[]
     */
    public function getItems()
    {
        return array_map(function (GridRowItem $rowItem) {
            return $rowItem->getItem();
        }, $this->rowItems->toArray());
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
        foreach ($this->rowItems as $rowItem) {
            if ($rowItem->getItem()->getUuid() === $uuid) {
                $found = $rowItem->getItem();
                break;
            }
        }

        return $found;
    }

    public function getItemIds()
    {
        return array_map(function (GridRowItem $rowItem) {
            return $rowItem->getItem()->getUuid();
        }, $this->rowItems->toArray());
    }

    /**
     * Add item.
     *
     * @param GridItem $item
     * @param int      $order
     */
    public function addItem(GridItem $item, $order = null)
    {
        if (empty($this->getItem($item->getUuid()))) {
            $rowItem = new GridRowItem();
            $rowItem->setOrder(isset($order) || 0 === $order ? $order : $this->rowItems->count());
            $rowItem->setRow($this);
            $rowItem->setItem($item);

            $this->rowItems->add($rowItem);
        }
    }

    /**
     * Remove item.
     *
     * @param GridItem $item
     */
    public function removeItem(GridItem $item)
    {
        foreach ($this->rowItems as $rowItem) {
            if ($rowItem->getItem()->getUuid() === $item->getUuid()) {
                $this->rowItems->removeElement($rowItem);
                break;
            }
        }
    }

    /**
     * Get question.
     *
     * @return PairQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set question.
     *
     * @param PairQuestion $pairQuestion
     */
    public function setQuestion(PairQuestion $pairQuestion)
    {
        $this->question = $pairQuestion;
    }
}
