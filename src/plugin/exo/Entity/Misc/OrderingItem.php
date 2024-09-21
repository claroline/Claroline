<?php

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\OrderingQuestion;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\ContentTrait;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

/**
 * An ordering question.
 */
#[ORM\Table(name: 'ujm_ordering_item')]
#[ORM\Entity]
class OrderingItem implements AnswerPartInterface
{
    use Id;
    use ContentTrait;
    use FeedbackTrait;
    use ScoreTrait;
    use Uuid;

    /**
     * @var int
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private $position;

    #[ORM\JoinColumn(name: 'ujm_question_ordering_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: OrderingQuestion::class, inversedBy: 'items')]
    private $question;

    /**
     * OrderingItem constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
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
     * @return OrderingQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set question.
     */
    public function setQuestion(OrderingQuestion $question)
    {
        $this->question = $question;
    }
}
