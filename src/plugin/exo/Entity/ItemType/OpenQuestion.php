<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column]
    private string $contentType = 'text';

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $maxAnswerLength = 0;

    public function __construct()
    {
        $this->keywords = new ArrayCollection();
    }

    public function getKeywords(): Collection
    {
        return $this->keywords;
    }

    public function setKeywords(array $keywords): void
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

    public function addKeyword(Keyword $keyword): void
    {
        if (!$this->keywords->contains($keyword)) {
            $this->keywords->add($keyword);
            $keyword->setInteractionOpen($this);
        }
    }

    public function removeKeyword(Keyword $keyword): void
    {
        if ($this->keywords->contains($keyword)) {
            $this->keywords->removeElement($keyword);
        }
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): void
    {
        $this->contentType = $contentType;
    }

    public function setAnswerMaxLength(int $maxLength): void
    {
        $this->maxAnswerLength = $maxLength;
    }

    public function getAnswerMaxLength(): ?int
    {
        return $this->maxAnswerLength;
    }
}
