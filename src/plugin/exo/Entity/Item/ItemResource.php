<?php

namespace UJM\ExoBundle\Entity\Item;

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Content\OrderedResource;

/**
 * A Resource that can help to answer the Item.
 */
#[ORM\Table(name: 'ujm_question_resource')]
#[ORM\Entity]
class ItemResource extends OrderedResource
{
    use Id;

    /**
     * Owning Item.
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'resources')]
    private ?Item $question = null;

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
