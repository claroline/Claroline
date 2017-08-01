<?php

namespace UJM\ExoBundle\Entity\Misc;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\SelectionQuestion;

/**
 * Color.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_color")
 */
class Color
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The color code.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $colorCode;

    use UuidTrait;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\ItemType\SelectionQuestion", inversedBy="colors")
     * @ORM\JoinColumn(name="interaction_selection_id", referencedColumnName="id")
     */
    private $interactionSelection;

    /**
     * @ORM\OneToMany(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\ColorSelection",
     *     mappedBy="color", cascade={"persist", "remove"}, orphanRemoval=true
     * )
     */
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
            $colorSelection->setColor($this);
        }
    }

    /**
     * Removes a color selection.
     *
     * @param ColorSelection $colorSelection
     */
    public function removeColor(ColorSelection $colorSelection)
    {
        if ($this->colorSelections->contains($colorSelection)) {
            $this->colorSelections->removeElement($colorSelection);
        }
    }
}
