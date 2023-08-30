<?php

namespace Claroline\FlashcardBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(name="claro_flashcard_card")
 */
class Flashcard
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $question = null;

    /**
     * @ORM\Column(type="text")
     */
    private string $visibleContent;

    /**
     * @ORM\Column(type="text")
     */
    private string $hiddenContent;

    /**
     * @ORM\ManyToOne(targetEntity="FlashcardDeck", inversedBy="cards")
     *
     * @ORM\JoinColumn(nullable=false)
     */
    private FlashcardDeck $deck;

    /**
     * @ORM\OneToMany(targetEntity="UserProgression", mappedBy="flashcard")
     */
    private $progression;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(?string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getVisibleContent(): string
    {
        return $this->visibleContent;
    }

    public function setVisibleContent(string $visibleContent): self
    {
        $this->visibleContent = $visibleContent;

        return $this;
    }

    public function getHiddenContent(): string
    {
        return $this->hiddenContent;
    }

    public function setHiddenContent(string $hiddenContent): self
    {
        $this->hiddenContent = $hiddenContent;

        return $this;
    }

    public function getDeck(): FlashcardDeck
    {
        return $this->deck;
    }

    public function setDeck(FlashcardDeck $deck): self
    {
        $this->deck = $deck;

        return $this;
    }

    public function getProgressionByUser(User $user): ?UserProgression
    {
        foreach ($this->progression as $progression) {
            if ($progression->getUser() === $user) {
                return $progression;
            }
        }

        return null;
    }
}
