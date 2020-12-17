<?php

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\PairQuestion;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

/**
 * GridOdd.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_grid_odd")
 */
class GridOdd implements AnswerPartInterface
{
    /**
     * Unique identifier of the odd.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    use ScoreTrait;

    use FeedbackTrait;

    /**
     * The item which is odd.
     *
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Misc\GridItem")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     *
     * @var GridItem
     */
    private $item;

    /**
     * The parent question.
     *
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\ItemType\PairQuestion", inversedBy="oddItems")
     * @ORM\JoinColumn(name="pair_question_id", referencedColumnName="id")
     *
     * @var PairQuestion
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
     *
     * @param GridItem $item
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
     *
     * @param PairQuestion $pairQuestion
     */
    public function setQuestion(PairQuestion $pairQuestion)
    {
        $this->question = $pairQuestion;
    }
}
