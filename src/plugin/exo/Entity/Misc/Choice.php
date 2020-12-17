<?php

namespace UJM\ExoBundle\Entity\Misc;

use Claroline\AppBundle\Entity\Meta\Order;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\ChoiceQuestion;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;

/**
 * Choice.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_choice")
 */
class Choice extends AbstractChoice implements AnswerPartInterface
{
    use Order;
    /**
     * The choice is part of the expected answer for the question.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $expected = false;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\ItemType\ChoiceQuestion", inversedBy="choices")
     * @ORM\JoinColumn(name="interaction_qcm_id", referencedColumnName="id")
     */
    private $interactionQCM;

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

    public function setInteractionQCM(ChoiceQuestion $interactionQCM)
    {
        $this->interactionQCM = $interactionQCM;
    }
}
