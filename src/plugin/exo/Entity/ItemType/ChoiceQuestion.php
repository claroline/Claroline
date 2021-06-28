<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Misc\Choice;
use UJM\ExoBundle\Library\Model\ShuffleTrait;
use UJM\ExoBundle\Library\Options\Direction;
use UJM\ExoBundle\Library\Options\ExerciseNumbering;

/**
 * A choice question.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_interaction_qcm")
 */
class ChoiceQuestion extends AbstractItem
{
    use ShuffleTrait;
    /**
     * Is it a multiple or a unique choice question ?
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $multiple = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\Choice",
     *     mappedBy="interactionQCM",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"order" = "ASC"})
     */
    private $choices;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $numbering = ExerciseNumbering::NONE;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $direction = Direction::VERTICAL;

    /**
     * ChoiceQuestion constructor.
     */
    public function __construct()
    {
        $this->choices = new ArrayCollection();
    }

    /**
     * Is multiple ?
     *
     * @return bool
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * Sets multiple.
     *
     * @param bool $multiple
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;
    }

    /**
     * @return ArrayCollection
     */
    public function getChoices()
    {
        return $this->choices;
    }

    public function setChoices(ArrayCollection $choices)
    {
        $this->choices = $choices;
    }

    public function addChoice(Choice $choice)
    {
        if (!$this->choices->contains($choice)) {
            $this->choices->add($choice);
            $choice->setInteractionQCM($this);
        }
    }

    public function removeChoice(Choice $choice)
    {
        if ($this->choices->contains($choice)) {
            $this->choices->removeElement($choice);
        }
    }

    public function setNumbering($numbering)
    {
        $this->numbering = $numbering;
    }

    public function getNumbering()
    {
        return $this->numbering;
    }

    /**
     * @param string $direction
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
}
