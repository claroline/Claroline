<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Misc\Choice;
use UJM\ExoBundle\Library\Model\ShuffleTrait;
use UJM\ExoBundle\Library\Options\Direction;
use UJM\ExoBundle\Library\Options\ExerciseNumbering;

/**
 * A choice question.
 */
#[ORM\Table(name: 'ujm_interaction_qcm')]
#[ORM\Entity]
class ChoiceQuestion extends AbstractItem
{
    use ShuffleTrait;

    /**
     * Is it a multiple or a unique choice question ?
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $multiple = false;

    /**
     * @var Collection<int, Choice>
     */
    #[ORM\OneToMany(targetEntity: Choice::class, mappedBy: 'interactionQCM', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['order' => 'ASC'])]
    private Collection $choices;

    #[ORM\Column(type: Types::STRING)]
    private string $numbering = ExerciseNumbering::NONE;

    #[ORM\Column(type: Types::STRING)]
    private string $direction = Direction::VERTICAL;

    public function __construct()
    {
        $this->choices = new ArrayCollection();
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function setMultiple(bool $multiple): void
    {
        $this->multiple = $multiple;
    }

    public function getChoices(): Collection
    {
        return $this->choices;
    }

    public function setChoices(ArrayCollection $choices): void
    {
        $this->choices = $choices;
    }

    public function addChoice(Choice $choice): void
    {
        if (!$this->choices->contains($choice)) {
            $this->choices->add($choice);
            $choice->setInteractionQCM($this);
        }
    }

    public function removeChoice(Choice $choice): void
    {
        if ($this->choices->contains($choice)) {
            $this->choices->removeElement($choice);
        }
    }

    public function setNumbering(string $numbering): void
    {
        $this->numbering = $numbering;
    }

    public function getNumbering(): string
    {
        return $this->numbering;
    }

    public function setDirection(string $direction): void
    {
        $this->direction = $direction;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }
}
