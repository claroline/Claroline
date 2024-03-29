<?php

namespace Claroline\FlashcardBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\HasEndPage;
use Claroline\CoreBundle\Entity\Resource\HasHomePage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(name="claro_flashcard_deck")
 */
class FlashcardDeck extends AbstractResource
{
    use HasHomePage;
    use HasEndPage;

    /**
     * @var ArrayCollection|Flashcard[]
     *
     * @ORM\OneToMany(targetEntity="Flashcard", mappedBy="deck", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $cards;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $draw = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $showProgression = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $customButtons = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $rightButtonLabel = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $wrongButtonLabel = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $showLeitnerRules = false;

    public function __construct()
    {
        parent::__construct();
        $this->cards = new ArrayCollection();
    }

    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function getCardByUuid(string $uuid): ?Flashcard
    {
        foreach ($this->cards as $card) {
            if ($card->getUuid() === $uuid) {
                return $card;
            }
        }

        return null;
    }

    public function hasCard(Flashcard $card): bool
    {
        return $this->cards->contains($card);
    }

    public function addCard(Flashcard $card): void
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
            $card->setDeck($this);
        }
    }

    public function removeCard(Flashcard $card): void
    {
        if ($this->cards->contains($card)) {
            $this->cards->removeElement($card);
        }
    }

    public function getDraw(): ?int
    {
        return $this->draw;
    }

    public function setDraw($draw): void
    {
        $this->draw = $draw;
    }

    public function getShowProgression(): bool
    {
        return $this->showProgression;
    }

    public function setShowProgression(bool $showProgression): void
    {
        $this->showProgression = $showProgression;
    }

    public function getCustomButtons(): bool
    {
        return $this->customButtons;
    }

    public function setCustomButtons(bool $customButtons): void
    {
        $this->customButtons = $customButtons;
    }

    public function getRightButtonLabel(): ?string
    {
        return $this->rightButtonLabel;
    }

    public function setRightButtonLabel(?string $rightButtonLabel): void
    {
        $this->rightButtonLabel = $rightButtonLabel;
    }

    public function getWrongButtonLabel(): ?string
    {
        return $this->wrongButtonLabel;
    }

    public function setWrongButtonLabel(?string $wrongButtonLabel): void
    {
        $this->wrongButtonLabel = $wrongButtonLabel;
    }

    public function getShowLeitnerRules(): bool
    {
        return $this->showLeitnerRules;
    }

    public function setShowLeitnerRules(bool $showLeitnerRules): void
    {
        $this->showLeitnerRules = $showLeitnerRules;
    }
}
