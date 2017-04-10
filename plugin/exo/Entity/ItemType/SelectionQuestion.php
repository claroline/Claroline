<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Misc\Color;
use UJM\ExoBundle\Entity\Misc\Selection;

/**
 * A Selection question.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_interaction_selection")
 */
class SelectionQuestion extends AbstractItem
{
    const MODE_HIGHLIGHT = 'highlight';
    const MODE_FIND = 'find';
    const MODE_SELECT = 'select';

    /**
     * The HTML text.
     *
     * @ORM\Column(name="text", type="text")
     *
     * @var string
     */
    private $text;

    /**
     * The selection question mode.
     *
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    private $mode = self::MODE_SELECT;
    /**
     * The max amount of tries for find mode.
     *
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $tries = 0;

    /**
     * @ORM\Column(type="float", nullable=true)
     *
     * @var float
     */
    private $penalty = null;

    /**
     * The list of selections present in the text.
     *
     * @ORM\OneToMany(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\Selection",
     *     mappedBy="interactionSelection",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     *
     * @var ArrayCollection
     */
    private $selections;

    /**
     * @ORM\OneToMany(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\Color",
     *     mappedBy="interactionSelection",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    private $colors;

    /**
     * SelectionQuestion constructor.
     */
    public function __construct()
    {
        $this->selections = new ArrayCollection();
        $this->colors = new ArrayCollection();
    }

    /**
     * Gets text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Sets text.
     *
     * @param $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Set mode.
     *
     * @param $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * Get mode.
     *
     * @param $mode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set tries.
     *
     * @param $tries
     */
    public function setTries($tries)
    {
        $this->tries = $tries;
    }

    /**
     * Get tries.
     *
     * @param $tries
     */
    public function getTries()
    {
        return $this->tries;
    }

    /**
     * Gets selections.
     *
     * @return ArrayCollection
     */
    public function getSelections()
    {
        return $this->selections;
    }

    /**
     * Retrieves a selection by its uuid.
     *
     * @param $uuid
     *
     * @return Selection
     */
    public function getSelection($uuid)
    {
        foreach ($this->selections as $selection) {
            if ($selection->getUuid() === $uuid) {
                return $selection;
            }
        }
    }

    public function getColorSelections()
    {
        return array_reduce($this->getSelections()->toArray(), function ($acc, $el) {
            return array_merge($acc, $el->getColorSelections()->toArray());
        }, []);
    }

    public function getColorSelection(array $options)
    {
        $colorUuid = isset($options['color_uuid']) ? $options['color_uuid'] : null;
        $selectionUuid = isset($options['selection_uuid']) ? $options['selection_uuid'] : null;

        foreach ($this->selections as $selection) {
            foreach ($selection->getColorSelections() as $colorSelection) {
                if ($colorSelection->getColor()->getUuid() === $colorUuid && $colorSelection->getSelection()->getUuid() === $selectionUuid) {
                    return $colorSelection;
                }
            }
        }
    }

    /**
     * Adds a selection.
     *
     * @param Selection $selection
     */
    public function addSelection(Selection $selection)
    {
        if (!$this->selections->contains($selection)) {
            $this->selections->add($selection);
            $selection->setInteractionSelection($this);
        }
    }

    /**
     * Removes a selection.
     *
     * @param Selection $selection
     */
    public function removeSelection(Selection $selection)
    {
        if ($this->selections->contains($selection)) {
            $this->selections->removeElement($selection);
        }
    }

    /**
     * Gets colors.
     *
     * @return ArrayCollection
     */
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * Retrieves a color by its uuid.
     *
     * @param $uuid
     *
     * @return Color
     */
    public function getColor($uuid)
    {
        foreach ($this->colors as $color) {
            if ($color->getUuid() === $uuid) {
                return $color;
            }
        }
    }

    /**
     * Adds a color.
     *
     * @param Color $color
     */
    public function addColor(Color $color)
    {
        if (!$this->colors->contains($color)) {
            $this->colors->add($color);
            $color->setInteractionSelection($this);
        }
    }

    /**
     * Removes a color.
     *
     * @param Color $color
     */
    public function removeColor(Color $color)
    {
        if ($this->colors->contains($color)) {
            $this->colors->removeElement($color);
        }
    }

    public function setPenalty($penalty)
    {
        $this->penalty = $penalty;
    }

    public function getPenalty()
    {
        return $this->penalty;
    }
}
