<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Misc\Choice;
use UJM\ExoBundle\Library\Model\ShuffleTrait;

/**
 * A choice question.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_interaction_qcm")
 */
class ChoiceQuestion extends AbstractItem
{
    /**
     * Is it a multiple or a unique choice question ?
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $multiple = false;

    use ShuffleTrait;

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
     * Constructs a new instance of choices.
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

    /**
     * @param Choice $choice
     */
    public function addChoice(Choice $choice)
    {
        if (!$this->choices->contains($choice)) {
            $this->choices->add($choice);
            $choice->setInteractionQCM($this);
        }
    }

    /**
     * @param Choice $choice
     */
    public function removeChoice(Choice $choice)
    {
        if ($this->choices->contains($choice)) {
            $this->choices->removeElement($choice);
        }
    }
}
