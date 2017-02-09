<?php

namespace UJM\ExoBundle\Entity\Item;

use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Content\OrderedResource;

/**
 * A Resource that can help to answer the Item.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_question_resource")
 */
class ItemResource extends OrderedResource
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
     * Owning Item.
     *
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Item\Item", inversedBy="resources")
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
     * @param Item $question
     */
    public function setQuestion(Item $question = null)
    {
        $this->question = $question;
    }

    /**
     * Get question.
     *
     * @return Item
     */
    public function getQuestion()
    {
        return $this->question;
    }
}
