<?php

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use UJM\ExoBundle\Entity\QuestionType\ChoiceQuestion;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\ContentTrait;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\OrderTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;
use UJM\ExoBundle\Library\Model\UuidTrait;

/**
 * Choice.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_choice")
 */
class Choice implements AnswerPartInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    use UuidTrait;

    use OrderTrait;

    use ScoreTrait;

    use FeedbackTrait;

    use ContentTrait;

    /**
     * The choice is part of the expected answer for the question.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $expected = false;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\QuestionType\ChoiceQuestion", inversedBy="choices")
     * @ORM\JoinColumn(name="interaction_qcm_id", referencedColumnName="id")
     */
    private $interactionQCM;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets expected.
     *
     * @param bool $expected
     */
    public function setExpected($expected)
    {
        $this->expected = $expected;
    }

    /**
     * Is expected ?
     *
     * @return bool
     */
    public function isExpected()
    {
        return $this->expected;
    }

    /**
     * @return ChoiceQuestion
     */
    public function getInteractionQCM()
    {
        return $this->interactionQCM;
    }

    /**
     * @param ChoiceQuestion $interactionQCM
     */
    public function setInteractionQCM(ChoiceQuestion $interactionQCM)
    {
        $this->interactionQCM = $interactionQCM;
    }
}
