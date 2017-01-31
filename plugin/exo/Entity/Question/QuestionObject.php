<?php

namespace UJM\ExoBundle\Entity\Question;

use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Content\OrderedResource;

/**
 * A Resource on which the Question is referred.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_object_question")
 */
class QuestionObject extends OrderedResource
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Owning Question.
     *
     * @var Question
     *
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Question\Question", inversedBy="objects")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $question;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set question.
     *
     * @param Question $question
     */
    public function setQuestion(Question $question = null)
    {
        $this->question = $question;
    }

    /**
     * Get question.
     *
     * @return Question
     */
    public function getQuestion()
    {
        return $this->question;
    }
}
