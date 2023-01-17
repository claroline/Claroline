<?php

namespace UJM\ExoBundle\Entity\Misc;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\ClozeQuestion;
use UJM\ExoBundle\Library\Model\ShuffleTrait;

/**
 * Hole.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_hole")
 */
class Hole
{
    use Uuid;
    use ShuffleTrait;

    /**
     * The identifier of the hole.
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The display size of the hole input.
     *
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $size = 0;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $selector = false;

    /**
     * The help text to display in the empty hole input.
     *
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    private $placeholder;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\ItemType\ClozeQuestion", inversedBy="holes")
     * @ORM\JoinColumn(name="interaction_hole_id", referencedColumnName="id")
     */
    private $interactionHole;

    /**
     * The list of keywords attached to the hole.
     *
     * @ORM\OneToMany(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\Keyword",
     *     mappedBy="hole",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     *
     * @var ArrayCollection
     */
    private $keywords;

    /**
     * Hole constructor.
     */
    public function __construct()
    {
        $this->keywords = new ArrayCollection();
        $this->refreshUuid();
    }

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
     * Set size.
     *
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * Get size.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set selector.
     *
     * @param int $selector
     */
    public function setSelector($selector)
    {
        $this->selector = $selector;
    }

    /**
     * Get selector.
     */
    public function getSelector()
    {
        return $this->selector;
    }

    /**
     * Get placeholder.
     *
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * Set placeholder.
     *
     * @param string $placeholder
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
    }

    public function getInteractionHole()
    {
        return $this->interactionHole;
    }

    public function setInteractionHole(ClozeQuestion $interactionHole)
    {
        $this->interactionHole = $interactionHole;
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
     * Get a keyword by text.
     *
     * @param string $text
     *
     * @return Keyword
     */
    public function getKeyword($text)
    {
        $found = null;
        $text = trim($text);
        $iText = strtoupper(TextNormalizer::stripDiacritics($text));
        foreach ($this->keywords as $keyword) {
            /** @var Keyword $keyword */
            $tmpText = trim($keyword->getText());
            if ($tmpText === $text
                || (
                    empty($keyword->isCaseSensitive()) &&
                    strtoupper(TextNormalizer::stripDiacritics($tmpText)) === $iText)
            ) {
                $found = $keyword;
                break;
            }
        }

        return $found;
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
     */
    public function addKeyword(Keyword $keyword)
    {
        if (!$this->keywords->contains($keyword)) {
            $this->keywords->add($keyword);
            $keyword->setHole($this);
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
}
