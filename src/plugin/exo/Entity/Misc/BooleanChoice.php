<?php

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\BooleanQuestion;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;

/**
 * Choice.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_boolean_choice")
 */
class BooleanChoice extends AbstractChoice implements AnswerPartInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\ItemType\BooleanQuestion", inversedBy="choices")
     * @ORM\JoinColumn(name="boolean_question_id", referencedColumnName="id")
     */
    private $question;

    /**
     * @return BooleanQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param BooleanQuestion $question
     */
    public function setQuestion(BooleanQuestion $question)
    {
        $this->question = $question;
    }
}
