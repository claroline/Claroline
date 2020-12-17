<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Item\Item;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractItem
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="UJM\ExoBundle\Entity\Item\Item")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Item
     */
    protected $question;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Item $question
     */
    final public function setQuestion(Item $question)
    {
        $this->question = $question;

        $question->setInteraction($this);
    }

    /**
     * @return Item
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @return bool
     */
    public function isContentItem()
    {
        return false;
    }
}
