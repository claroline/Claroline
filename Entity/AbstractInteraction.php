<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractInteraction implements QuestionTypeProviderInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Question", cascade={"remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $question;

    /**
     * @return integer
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
        $question->setType(static::getQuestionType());
    }

    /**
     * @return Question
     */
    public function getQuestion()
    {
        return $this->question;
    }
}
