<?php

namespace UJM\ExoBundle\Entity\Misc;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\SelectionQuestion;

/**
 * Color.
 */
#[ORM\Table(name: 'ujm_color')]
#[ORM\Entity]
class Color
{
    use Id;
    use Uuid;

    /**
     * The color code.
     *
     * @var string
     */
    #[ORM\Column(type: 'string')]
    private $colorCode;

    #[ORM\JoinColumn(name: 'interaction_selection_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \UJM\ExoBundle\Entity\ItemType\SelectionQuestion::class, inversedBy: 'colors')]
    private $interactionSelection;

    #[ORM\OneToMany(targetEntity: \UJM\ExoBundle\Entity\Misc\ColorSelection::class, mappedBy: 'color', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private $colorSelections;

    public function __construct()
    {
        $this->refreshUuid();
        $this->colorSelections = new ArrayCollection();
    }

    public function setColorCode($colorCode)
    {
        $this->colorCode = $colorCode;
    }

    public function getColorCode()
    {
        return $this->colorCode;
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
            $colorSelection->setColor($this);
        }
    }

    /**
     * Removes a color selection.
     */
    public function removeColor(ColorSelection $colorSelection)
    {
        if ($this->colorSelections->contains($colorSelection)) {
            $this->colorSelections->removeElement($colorSelection);
        }
    }
}
