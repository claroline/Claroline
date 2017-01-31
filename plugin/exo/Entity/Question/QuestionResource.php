<?php

namespace UJM\ExoBundle\Entity\Question;

use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Content\OrderedResource;

/**
 * A Resource that can help to answer the Question.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_question_resource")
 */
class QuestionResource extends OrderedResource
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
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Question\Question", inversedBy="resources")
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
