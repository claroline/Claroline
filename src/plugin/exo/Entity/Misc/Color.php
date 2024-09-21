<?php

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
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
    #[ORM\Column(type: Types::STRING)]
    private $colorCode;

    #[ORM\JoinColumn(name: 'interaction_selection_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: SelectionQuestion::class, inversedBy: 'colors')]
    private ?SelectionQuestion $interactionSelection = null;

    /**
     * @var Collection<int, ColorSelection>
     */
    #[ORM\OneToMany(targetEntity: ColorSelection::class, mappedBy: 'color', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $colorSelections;

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
