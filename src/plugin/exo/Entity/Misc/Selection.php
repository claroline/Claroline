<?php

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\SelectionQuestion;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

/**
 * Selection.
 */
#[ORM\Table(name: 'ujm_selection')]
#[ORM\Entity]
class Selection implements AnswerPartInterface
{
    use Id;
    use FeedbackTrait;
    use ScoreTrait;
    use Uuid;

    /**
     * The starting position.
     *
     * @var int
     */
    #[ORM\Column(type: Types::INTEGER)]
    private $begin;

    /**
     * The ending position.
     *
     * @var int
     */
    #[ORM\Column(type: Types::INTEGER)]
    private $end;

    #[ORM\JoinColumn(name: 'interation_selection_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: SelectionQuestion::class, inversedBy: 'selections')]
    private ?SelectionQuestion $interactionSelection = null;

    /**
     * @var Collection<int, ColorSelection>
     */
    #[ORM\OneToMany(targetEntity: ColorSelection::class, mappedBy: 'selection', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $colorSelections;

    public function __construct()
    {
        $this->refreshUuid();
        $this->colorSelections = new ArrayCollection();
    }

    /**
     * @return SelectionQuestion
     */
    public function getInteractionSelection()
    {
        return $this->interactionSelection;
    }

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
     */
    public function removeColorSelection(ColorSelection $colorSelection)
    {
        if ($this->colorSelections->contains($colorSelection)) {
            $this->colorSelections->removeElement($colorSelection);
        }
    }
}
