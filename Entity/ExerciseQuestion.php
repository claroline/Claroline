<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\ExerciseQuestion.
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\ExerciseQuestionRepository")
 * @ORM\Table(name="ujm_exercise_question")
 */
class ExerciseQuestion
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Exercise")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $exercise;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Question")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $question;

    /**
     * @var int
     *
     * @ORM\Column(name="ordre", type="integer")
     */
    private $ordre;

    public function __construct(\UJM\ExoBundle\Entity\Exercise $exercise, \UJM\ExoBundle\Entity\Question $question)
    {
        $this->exercise = $exercise;
        $this->question = $question;
    }

    public function setExercise(\UJM\ExoBundle\Entity\Exercise $exercise)
    {
        $this->exercise = $exercise;
    }

    public function getExercise()
    {
        return $this->exercise;
    }

    public function setQuestion(\UJM\ExoBundle\Entity\Question $question)
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
