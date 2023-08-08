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

    public function __construct()
    {
        parent::__construct();
        $this->cards = new ArrayCollection();
    }

    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Flashcard $card): void
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
        }
    }

    public function removeCard(Flashcard $card): void
    {
        $this->cards->removeElement($card);
    }
}
