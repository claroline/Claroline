<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\StepQuestion.
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\StepQuestionRepository")
 * @ORM\Table(name="ujm_step_question")
 */

class StepQuestion {

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Step")
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
     * @var int
     *
     * @ORM\Column(name="ordre", type="integer")
     */
    private $ordre;

    public function __construct(Step $step, Question $question)
    {
        $this->step = $step;
        $this->question = $question;
    }

    public function setStep(Step $step)
    {
        $this->step = $step;
    }

    public function getStep()
    {
        return $this->step;
    }

    public function setQuestion(Question $question)
    {
        $this->question = $question;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set ordre.
     *
     * @param int $ordre
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;
    }

    /**
     * Get ordre.
     *
     * @return int
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

}
