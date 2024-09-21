<?php

namespace UJM\ExoBundle\Entity\Misc;

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\PairQuestion;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

/**
 * GridOdd.
 */
#[ORM\Table(name: 'ujm_grid_odd')]
#[ORM\Entity]
class GridOdd implements AnswerPartInterface
{
    use Id;
    use ScoreTrait;
    use FeedbackTrait;

    /**
     * The item which is odd.
     *
     *
     * @var GridItem
     */
    #[ORM\JoinColumn(name: 'item_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \UJM\ExoBundle\Entity\Misc\GridItem::class)]
    private $item;

    /**
     * The parent question.
     *
     *
     * @var PairQuestion
     */
    #[ORM\JoinColumn(name: 'pair_question_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \UJM\ExoBundle\Entity\ItemType\PairQuestion::class, inversedBy: 'oddItems')]
    private $question;

    /**
     * Get item.
     *
     * @return GridItem
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set item.
     */
    public function setItem(GridItem $item)
    {
        $this->item = $item;
    }

    /**
     * Get question.
     *
     * @return PairQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set question.
     */
    public function setQuestion(PairQuestion $pairQuestion)
    {
        $this->question = $pairQuestion;
    }
}
