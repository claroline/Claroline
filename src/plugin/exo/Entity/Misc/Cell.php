<?php

namespace UJM\ExoBundle\Entity\Misc;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\GridQuestion;
use UJM\ExoBundle\Library\Model\ShuffleTrait;

/**
 * GridItem.
 */
#[ORM\Table(name: 'ujm_cell')]
#[ORM\Entity]
class Cell
{
    use Id;
    use Uuid;
    use ShuffleTrait;

    /**
     * Data associated to the cell.
     *
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $data = null;

    /**
     * X coordinate of the item in the grid.
     *
     *
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $coordsX = null;

    /**
     * Y coordinate of the item in the grid.
     *
     *
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $coordsY = null;

    /**
     * Font color in the cell.
     *
     *
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: false)]
    private $color = '#000';

    /**
     * Cell background color.
     *
     *
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: false)]
    private $background = '#fff';

    /**
     * The list of texts attached to the cell.
     *
     *
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: \UJM\ExoBundle\Entity\Misc\CellChoice::class, mappedBy: 'cell', cascade: ['all'], orphanRemoval: true)]
    private $choices;

    #[ORM\JoinColumn(name: 'question_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \UJM\ExoBundle\Entity\ItemType\GridQuestion::class, inversedBy: 'cells')]
    private $question;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private $selector = false;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private $input = false;

    /**
     * Cell constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
        $this->choices = new ArrayCollection();
    }

    public function setQuestion(GridQuestion $question)
    {
        $this->question = $question;
    }

    /**
     * @return GridQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Set selector.
     *
     * @param bool $selector
     */
    public function setSelector($selector)
    {
        $this->selector = $selector;
    }

    /**
     * Get selector.
     */
    public function isSelector()
    {
        return $this->selector;
    }

    /**
     * Get input.
     */
    public function isInput()
    {
        return $this->input;
    }

    /**
     * Set input.
     *
     * @param bool $input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }

    /**
     * Get X coordinate.
     *
     * @return int
     */
    public function getCoordsX()
    {
        return $this->coordsX;
    }

    /**
     * Set X coordinate.
     *
     * @param int $coordsX
     */
    public function setCoordsX($coordsX)
    {
        $this->coordsX = $coordsX;
    }

    /**
     * Get Y coordinate.
     *
     * @return int
     */
    public function getCoordsY()
    {
        return $this->coordsY;
    }

    /**
     * Set Y coordinate.
     *
     * @param $coordsY
     */
    public function setCoordsY($coordsY)
    {
        $this->coordsY = $coordsY;
    }

    /**
     * Get coordinates.
     *
     * @return array
     */
    public function getCoords()
    {
        return (is_int($this->coordsX) || is_int($this->coordsY)) ?
            [$this->coordsX, $this->coordsY] : null;
    }

    /**
     * Cell background color.
     *
     * @param string $color
     */
    public function setBackground($color)
    {
        $this->background = $color;
    }

    /**
     * @return string
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * Cell font color.
     *
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Get styles for the cell.
     *
     * @return array
     */
    public function getCellStyle()
    {
        return ['background' => $this->background, 'color' => $this->color];
    }

    /**
     * Sets choices collection.
     */
    public function setChoices(array $choices)
    {
        // Removes old choices
        $oldChoices = array_filter($this->choices->toArray(), function (CellChoice $choice) use ($choices) {
            return !in_array($choice, $choices);
        });
        array_walk($oldChoices, function (CellChoice $choice): void {
            $this->removeChoice($choice);
        });

        // Adds new ones
        array_walk($choices, function (CellChoice $choice): void {
            $this->addChoice($choice);
        });
    }

    public function addChoice(CellChoice $choice)
    {
        if (!$this->choices->contains($choice)) {
            $choice->setCell($this);
            $this->choices->add($choice);
        }
    }

    public function removeChoice(CellChoice $choice)
    {
        if ($this->choices->contains($choice)) {
            $this->choices->removeElement($choice);
        }
    }

    /**
     * Get a cell choice by text.
     *
     * @param string $text
     *
     * @return CellChoice|null
     */
    public function getChoice($text)
    {
        $found = null;
        $text = trim($text);
        $iText = strtoupper(TextNormalizer::stripDiacritics($text));
        foreach ($this->choices as $choice) {
            /** @var CellChoice $choice */
            $tmpText = trim($choice->getText());
            if ($tmpText === $text
              || (
                  !$choice->isCaseSensitive() &&
                  strtoupper(TextNormalizer::stripDiacritics($tmpText)) === $iText)
          ) {
                $found = $choice;
                break;
            }
        }

        return $found;
    }

    /**
     * @return ArrayCollection
     */
    public function getChoices()
    {
        return $this->choices;
    }
}
