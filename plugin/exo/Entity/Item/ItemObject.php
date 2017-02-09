<?php

namespace UJM\ExoBundle\Entity\Item;

use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Content\OrderedResource;

/**
 * A Resource on which the Item is referred.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_object_question")
 */
class ItemObject extends OrderedResource
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
     * @var Item
     *
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Item\Item", inversedBy="objects")
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
