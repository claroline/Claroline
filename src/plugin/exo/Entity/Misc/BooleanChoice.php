<?php

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\BooleanQuestion;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;

/**
 * Choice.
 */
#[ORM\Table(name: 'ujm_boolean_choice')]
#[ORM\Entity]
class BooleanChoice extends AbstractChoice implements AnswerPartInterface
{
    #[ORM\JoinColumn(name: 'boolean_question_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: BooleanQuestion::class, inversedBy: 'choices')]
    private $question;

    /**
     * @return BooleanQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    public function setQuestion(BooleanQuestion $question)
    {
        $this->question = $question;
    }
}
