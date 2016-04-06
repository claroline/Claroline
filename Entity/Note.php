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

/**
 * Note
 *
 * @ORM\Table(name="claro_fcbundle_note")
 * @ORM\Entity
 */
class Note
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="NoteType", inversedBy="notes")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $noteType;

    /**
     * @ORM\ManyToOne(targetEntity="Deck", inversedBy="notes")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $deck;

    /**
     * @ORM\OneToMany(targetEntity="FieldValue", mappedBy="note")
     */
    private $fieldValues;

    /**
     * @ORM\OneToMany(targetEntity="Card", mappedBy="note")
     */
    private $cards;

    public function __construct()
    {
        $this->fieldValues = new ArrayCollection();
        $this->cards = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
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
     * @return ArrayCollection
     */
    public function getCards()
    {
        return $this->cards;
    }

}
