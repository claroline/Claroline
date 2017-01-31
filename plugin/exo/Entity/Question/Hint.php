<?php

namespace UJM\ExoBundle\Entity\Question;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use UJM\ExoBundle\Library\Attempt\PenaltyItemInterface;
use UJM\ExoBundle\Library\Model\ContentTrait;
use UJM\ExoBundle\Library\Model\PenaltyTrait;
use UJM\ExoBundle\Library\Model\UuidTrait;

/**
 * Hint.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_hint")
 */
class Hint implements PenaltyItemInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    use UuidTrait;

    use ContentTrait;

    use PenaltyTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Question", inversedBy="hints")
     *
     * @var Question
     */
    private $question;

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
     * @param Question $question
     */
    public function setQuestion(Question $question)
    {
        $this->question = $question;
    }

    /**
     * @return Question
     */
    public function getQuestion()
    {
        return $this->question;
    }
}
