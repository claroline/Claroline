<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Meta\Order;
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
     *
     *
     * @var Step
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Step::class, inversedBy: 'stepQuestions')]
    private ?Step $step = null;

    /**
     * The linked question.
     *
     *
     * @var Item
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Item::class, cascade: ['persist'])]
    private ?Item $question = null;

    /**
     * The answer is mandatory to continue the quiz.
     *
     * @var bool
     */
    #[ORM\Column(name: 'mandatory', type: Types::BOOLEAN, nullable: true)]
    private $mandatory = false;

    /**
     * Set Step.
     */
    public function setStep(Step $step)
    {
        $this->step = $step;

        $step->addStepQuestion($this);
    }

    /**
     * Get Step.
     *
     * @return Step
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set Item.
     */
    public function setQuestion(Item $question)
    {
        $this->question = $question;
    }

    /**
     * Get Item.
     *
     * @return Item
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @return bool
     */
    public function isMandatory()
    {
        return $this->mandatory;
    }

    /**
     * @param bool $mandatory
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
    }
}
