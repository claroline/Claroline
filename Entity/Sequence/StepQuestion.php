<?php

namespace UJM\ExoBundle\Entity\Sequence;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use UJM\ExoBundle\Entity\Question;

/**
 * Step Question relationship Entity.
 *
 * @ORM\Table(name="ujm_step_question")
 * @ORM\Entity
 */
class StepQuestion implements \JsonSerializable
{
    /**
     * @var step
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Sequence\Step", inversedBy="stepQuestions")
     * @ORM\JoinColumn(name="step_id")
     */
    protected $step;

    /**
     * @var question
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Question")
     * @ORM\JoinColumn(name="question_id")
     */
    protected $question;

    /**
     * @var Number
     *
     * @ORM\Column(name="position", type="smallint")
     * @Assert\NotBlank
     */
    protected $position;

    /**
     * @param Step $step
     *
     * @return \UJM\ExoBundle\Entity\Sequence\StepQuestion
     */
    public function setStep(Step $step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * @return \UJM\ExoBundle\Entity\Sequence\Step
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param Question $question
     *
     * @return \UJM\ExoBundle\Entity\Sequence\StepQuestion
     */
    public function setQuestion(\UJM\ExoBundle\Entity\Question $question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return \UJM\ExoBundle\Entity\Sequence\Sequence
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param number $position
     *
     * @return \UJM\ExoBundle\Entity\Sequence\StepQuestion
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return number
     */
    public function getPosition()
    {
        return $this->position;
    }

    public function jsonSerialize()
    {
        return array(
            'position' => $this->position,
            'questionId' => $this->question->getId(),
            'stepId' => $this->step->getId(),
        );
    }
}
