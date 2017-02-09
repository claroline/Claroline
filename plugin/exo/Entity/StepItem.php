<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Model\OrderTrait;

/**
 * A stepItem represents the link between a question and an exercise step.
 * It also stores the position of the question in the step.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_step_question")
 */
class StepItem
{
    /*
     * Keep the order of the question in the step.
     */
    use OrderTrait;

    /**
     * The parent step.
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Step", inversedBy="stepQuestions")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Step
     */
    private $step;

    /**
     * The linked question.
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Item\Item", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Item
     */
    private $question;

    /**
     * Set Step.
     *
     * @param Step $step
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
     *
     * @param Item $question
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
}
