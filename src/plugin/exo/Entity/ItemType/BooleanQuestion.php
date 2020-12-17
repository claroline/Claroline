<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Misc\BooleanChoice;

/**
 * A boolean question (ie true/false - yes/no ...).
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_boolean_question")
 */
class BooleanQuestion extends AbstractItem
{
    /**
     * @ORM\OneToMany(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\BooleanChoice",
     *     mappedBy="question",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
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
     * Get a choice by its uuid.
     *
     * @param $uuid
     *
     * @return BooleanChoice|null
     */
    public function getChoice($uuid)
    {
        $found = null;
        foreach ($this->choices as $choice) {
            if ($choice->getUuid() === $uuid) {
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

    /**
     * @param BooleanChoice $choice
     */
    public function addChoice(BooleanChoice $choice)
    {
        if (!$this->choices->contains($choice)) {
            $this->choices->add($choice);
            $choice->setQuestion($this);
        }
    }

    /**
     * @param BooleanChoice $choice
     */
    public function removeChoice(BooleanChoice $choice)
    {
        if ($this->choices->contains($choice)) {
            $this->choices->removeElement($choice);
        }
    }
}
