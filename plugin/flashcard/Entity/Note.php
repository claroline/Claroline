<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\FlashCardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * Note.
 *
 * @ORM\Table(name="claro_fcbundle_note")
 * @ORM\Entity
 */
class Note
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_note",
     *     "api_flashcard_card",
     *     "api_flashcard_deck"
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="NoteType", inversedBy="notes")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_note",
     *     "api_flashcard_deck"
     * })
     */
    private $noteType;

    /**
     * @ORM\ManyToOne(targetEntity="Deck", inversedBy="notes")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $deck;

    /**
     * @ORM\OneToMany(targetEntity="FieldValue", mappedBy="note")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_note",
     *     "api_flashcard_card",
     *     "api_flashcard_deck"
     * })
     */
    private $fieldValues;

    /**
     * @ORM\OneToMany(targetEntity="Card", mappedBy="note")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_note",
     *     "api_flashcard_deck"
     * })
     */
    private $cards;

    public function __construct()
    {
        $this->fieldValues = new ArrayCollection();
        $this->cards = new ArrayCollection();
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
     * @param NoteType $obj
     *
     * @return Note
     */
    public function setNoteType(NoteType $obj)
    {
        $this->noteType = $obj;

        return $this;
    }

    /**
     * @return NoteType
     */
    public function getNoteType()
    {
        return $this->noteType;
    }

    /**
     * @param Deck $obj
     *
     * @return Note
     */
    public function setDeck(Deck $obj)
    {
        $this->deck = $obj;

        return $this;
    }

    /**
     * @return Deck
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * @param FieldValue
     *
     * @return bool
     */
    public function addFieldValue(FieldValue $obj)
    {
        if ($this->fieldValues->contains($obj)) {
            return false;
        }
        if ($this->noteType->getFieldLabels()->contains($obj->getFieldLabel())) {
            return $this->fieldValues->add($obj);
        }

        return false;
    }

    /**
     * @param $fieldId
     * @param $value
     *
     * @return bool
     */
    public function setFieldValue($fieldId, $value)
    {
        foreach ($this->fieldValues as $f) {
            if ($f->getId() === $fieldId) {
                $f->setValue($value);

                return true;
            }
        }

        return false;
    }

    /**
     * @param ArrayCollection $obj
     *
     * @return Note
     */
    public function setFieldValues(ArrayCollection $obj)
    {
        $this->fieldValues = $obj;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getFieldValues()
    {
        return $this->fieldValues;
    }

    /**
     * @param Card
     *
     * @return bool
     */
    public function addCard(Card $obj)
    {
        if ($this->cards->contains($obj)) {
            return false;
        }
        if ($this->noteType->getCardTypes()->contains($obj->getCardType())) {
            return $this->cards->add($obj);
        }

        return false;
    }

    /**
     * @param ArrayCollection $obj
     *
     * @return Note
     */
    public function setCards(ArrayCollection $obj)
    {
        $this->cards = $obj;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getCards()
    {
        return $this->cards;
    }
}
