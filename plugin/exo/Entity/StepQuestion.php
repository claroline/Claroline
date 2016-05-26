<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\StepQuestion.
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\StepQuestionRepository")
 * @ORM\Table(name="ujm_step_question")
 */
class StepQuestion
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Step", inversedBy="stepQuestions")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $step;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Question", inversedBy="stepQuestions")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $question;

    /**
     * Order of the Question in the Step.
     *
     * @var int
     *
     * @ORM\Column(name="ordre", type="integer")
     */
    private $ordre;

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
     * Set Question.
     *
     * @param Question $question
     */
    public function setQuestion(Question $question)
    {
        $this->question = $question;
    }

    /**
     * Get Question.
     *
     * @return Question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set order.
     *
     * @param int $order
     */
    public function setOrdre($order)
    {
        $this->ordre = $order;
    }

    /**
     * Get order.
     *
     * @return int
     */
    public function getOrdre()
    {
        return $this->ordre;
    }
}
