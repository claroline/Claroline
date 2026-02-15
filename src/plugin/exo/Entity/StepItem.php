<?php

namespace UJM\ExoBundle\Entity;

use Claroline\AppBundle\Entity\Display\Order;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Item\Item;

/**
 * A stepItem represents the link between a question and an exercise step.
 * It also stores the position of the question in the step.
 */
#[ORM\Table(name: 'ujm_step_question')]
#[ORM\Entity]
class StepItem
{
    /*
     * Keep the order of the question in the step.
     */
    use Order;

    /**
     * The parent step.
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Step::class, inversedBy: 'stepQuestions')]
    private ?Step $step = null;

    /**
     * The linked question.
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Item::class, cascade: ['persist'])]
    private ?Item $question = null;

    /**
     * The answer is mandatory to continue the quiz.
     */
    #[ORM\Column(name: 'mandatory', type: Types::BOOLEAN, nullable: true)]
    private bool $mandatory = false;

    /**
     * Set Step.
     */
    public function setStep(Step $step): void
    {
        $this->step = $step;

        $step->addStepQuestion($this);
    }

    public function getStep(): ?Step
    {
        return $this->step;
    }

    public function setQuestion(Item $question): void
    {
        $this->question = $question;
    }

    public function getQuestion(): ?Item
    {
        return $this->question;
    }

    public function isMandatory(): bool
    {
        return $this->mandatory;
    }

    public function setMandatory(bool $mandatory): void
    {
        $this->mandatory = $mandatory;
    }
}
