<?php

namespace UJM\ExoBundle\Entity\Misc;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\OrderingQuestion;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\ContentTrait;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

/**
 * An ordering question.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_ordering_item")
 */
class OrderingItem implements AnswerPartInterface
{
    use UuidTrait;

    use ContentTrait;

    use ScoreTrait;

    use FeedbackTrait;

    /**
     * Unique identifier of the item.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $position;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\ItemType\OrderingQuestion", inversedBy="items")
     * @ORM\JoinColumn(name="ujm_question_ordering_id", referencedColumnName="id")
     */
    private $question;

    /**
     * OrderingItem constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

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
     * Get position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set position.
     *
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get question.
     *
     * @return Ordering
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set question.
     *
     * @param OrderingQuestion $question
     */
    public function setQuestion(OrderingQuestion $question)
    {
        $this->question = $question;
    }
}
