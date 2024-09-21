<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Item\Item;

#[ORM\MappedSuperclass]
abstract class AbstractItem
{
    use Id;
    /**
     *
     * @var Item
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: Item::class)]
    protected ?Item $question = null;

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
