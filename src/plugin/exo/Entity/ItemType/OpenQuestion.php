<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Misc\Keyword;

/**
 * An Open question.
 */
#[ORM\Table(name: 'ujm_interaction_open')]
#[ORM\Entity]
class OpenQuestion extends AbstractItem
{
    /**
     * @var Collection<int, Keyword>
     */
    #[ORM\OneToMany(targetEntity: Keyword::class, mappedBy: 'interactionopen', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $keywords;

    /**
     * @var string
     */
    #[ORM\Column]
    private $contentType = 'text';

    /**
     * The max allowed length fot answers to this question.
     *
     *
     * @var int
     */
    #[ORM\Column(type: Types::INTEGER)]
    private $maxAnswerLength = 0;

    /**
     * OpenQuestion constructor.
     */
    public function __construct()
    {
        $this->keywords = new ArrayCollection();
    }

    /**
     * Get keywords.
     *
     * @return ArrayCollection
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Sets keywords collection.
     */
    public function setKeywords(array $keywords)
    {
        // Removes old keywords
        $oldKeywords = array_filter($this->keywords->toArray(), function (Keyword $keyword) use ($keywords) {
            return !in_array($keyword, $keywords);
        });
        array_walk($oldKeywords, function (Keyword $keyword): void {
            $this->removeKeyword($keyword);
        });

        // Adds new ones
        array_walk($keywords, function (Keyword $keyword): void {
            $this->addKeyword($keyword);
        });
    }

    /**
     * Adds a keyword.
     */
    public function addKeyword(Keyword $keyword)
    {
        if (!$this->keywords->contains($keyword)) {
            $this->keywords->add($keyword);
            $keyword->setInteractionOpen($this);
        }
    }

    /**
     * Removes a keyword.
     */
    public function removeKeyword(Keyword $keyword)
    {
        if ($this->keywords->contains($keyword)) {
            $this->keywords->removeElement($keyword);
        }
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @param int $maxLength
     */
    public function setAnswerMaxLength($maxLength)
    {
        $this->maxAnswerLength = $maxLength;
    }

    /**
     * @return int
     */
    public function getAnswerMaxLength()
    {
        return $this->maxAnswerLength;
    }
}
