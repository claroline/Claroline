<?php

namespace UJM\ExoBundle\Entity\QuestionType;

use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Question\Question;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractQuestion
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="UJM\ExoBundle\Entity\Question\Question")
     *
     * @var Question
     */
    protected $question;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Question $question
     */
    final public function setQuestion(Question $question)
    {
        $this->question = $question;

        $question->setInteraction($this);
    }

    /**
     * @return Question
     */
    public function getQuestion()
    {
        return $this->question;
    }
}
