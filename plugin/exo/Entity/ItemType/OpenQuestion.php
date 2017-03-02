<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Misc\Keyword;

/**
 * An Open question.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_interaction_open")
 */
class OpenQuestion extends AbstractItem
{
    /**
     * @ORM\OneToMany(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\Keyword",
     *     mappedBy="interactionopen",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     *
     * @var ArrayCollection
     */
    private $keywords;

    /**
     * The max allowed length fot answers to this question.
     *
     * @ORM\Column(type="integer")
     *
     * @var int
     */
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
     *
     * @param array $keywords
     */
    public function setKeywords(array $keywords)
    {
        // Removes old keywords
        $oldKeywords = array_filter($this->keywords->toArray(), function (Keyword $keyword) use ($keywords) {
            return !in_array($keyword, $keywords);
        });
        array_walk($oldKeywords, function (Keyword $keyword) {
            $this->removeKeyword($keyword);
        });

        // Adds new ones
        array_walk($keywords, function (Keyword $keyword) {
            $this->addKeyword($keyword);
        });
    }

    /**
     * Adds a keyword.
     *
     * @param Keyword $keyword
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
     *
     * @param Keyword $keyword
     */
    public function removeKeyword(Keyword $keyword)
    {
        if ($this->keywords->contains($keyword)) {
            $this->keywords->removeElement($keyword);
        }
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
