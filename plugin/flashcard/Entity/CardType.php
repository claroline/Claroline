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
 * CardType.
 *
 * @ORM\Table(name="claro_fcbundle_card_type")
 * @ORM\Entity
 */
class CardType
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_card",
     *     "api_flashcard_deck"
     * })
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_card",
     *     "api_flashcard_note_type",
     *     "api_flashcard_deck"
     * })
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="FieldLabel")
     * @ORM\JoinTable(name="cardtype_fieldlabel_question")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_card",
     *     "api_flashcard_note_type",
     *     "api_flashcard_deck"
     * })
     */
    private $questions;

    /**
     * @ORM\ManyToMany(targetEntity="FieldLabel")
     * @ORM\JoinTable(name="cardtype_fieldlabel_answer")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_card",
     *     "api_flashcard_note_type",
     *     "api_flashcard_deck"
     * })
     */
    private $answers;

    /**
     * @ORM\ManyToOne(targetEntity="NoteType", inversedBy="cardTypes")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $noteType;

    /**
     * @ORM\OneToMany(targetEntity="Card", mappedBy="cardType")
     */
    private $cards;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->answers = new ArrayCollection();
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
     * @param string $name
     *
     * @return CardType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param FieldLabel
     *
     * @return bool
     */
    public function addQuestion(FieldLabel $obj)
    {
        if ($this->questions->contains($obj)) {
            return false;
        }
        if ($this->noteType === $obj->getNoteType()) {
            return $this->questions->add($obj);
        }

        return false;
    }

    /**
     * @param ArrayCollection
     *
     * @return CardType
     */
    public function setQuestions(ArrayCollection $obj)
    {
        $this->questions = $obj;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @param FieldLabel
     *
     * @return bool
     */
    public function addAnswer(FieldLabel $obj)
    {
        if ($this->answers->contains($obj)) {
            return false;
        }
        if ($this->noteType === $obj->getNoteType()) {
            return $this->answers->add($obj);
        }

        return false;
    }

    /**
     * @param ArrayCollection
     *
     * @return CardType
     */
    public function setAnswers(ArrayCollection $obj)
    {
        $this->answers = $obj;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * @param NoteType
     *
     * @return CardType
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
     * @return ArrayCollection
     */
    public function getCards()
    {
        return $this->cards;
    }
}
