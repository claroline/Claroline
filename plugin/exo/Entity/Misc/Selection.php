<?php

namespace UJM\ExoBundle\Entity\Misc;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\SelectionQuestion;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

/**
 * Selection.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_selection")
 */
class Selection implements AnswerPartInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    use UuidTrait;

    use ScoreTrait;

    use FeedbackTrait;

    /**
     * The starting position.
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $begin;

    /**
     * The ending position.
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $end;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\ItemType\SelectionQuestion", inversedBy="selections")
     * @ORM\JoinColumn(name="interation_selection_id", referencedColumnName="id")
     */
    private $interactionSelection;

    /**
     * @ORM\OneToMany(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\ColorSelection",
     *     mappedBy="selection",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    private $colorSelections;

    public function __construct()
    {
        $this->refreshUuid();
        $this->colorSelections = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return SelectionQuestion
     */
    public function getInteractionSelection()
    {
        return $this->interactionSelection;
    }

    /**
     * @param SelectionQuestion $interactionSelection
     */
    public function setInteractionSelection(SelectionQuestion $interactionSelection)
    {
        $this->interactionSelection = $interactionSelection;
    }

    public function setBegin($begin)
    {
        $this->begin = $begin;
    }

    public function getBegin()
    {
        return $this->begin;
    }

    public function setEnd($end)
    {
        $this->end = $end;
    }

    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Gets colors.
     *
     * @return ArrayCollection
     */
    public function getColorSelections()
    {
        return $this->colorSelections;
    }

    /**
     * Adds a color selection.
     *
     * @param ColorSelection $colorSelection
     */
    public function addColorSelection(ColorSelection $colorSelection)
    {
        if (!$this->colorSelections->contains($colorSelection)) {
            $this->colorSelections->add($colorSelection);
            $colorSelection->setSelection($this);
        }
    }

    /**
     * Removes a color selection.
     *
     * @param ColorSelection $colorSelection
     */
    public function removeColorSelection(ColorSelection $colorSelection)
    {
        if ($this->colorSelections->contains($colorSelection)) {
            $this->colorSelections->removeElement($colorSelection);
        }
    }
}
