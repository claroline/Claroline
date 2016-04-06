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
 * CardType
 *
 * @ORM\Table(name="claro_fcbundle_card_type")
 * @ORM\Entity
 */
class CardType
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="FieldLabel")
     * @ORM\JoinTable(name="cardtype_fieldlabel_question")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $questions;

    /**
     * @ORM\ManyToMany(targetEntity="FieldLabel")
     * @ORM\JoinTable(name="cardtype_fieldlabel_answer")
     * @ORM\JoinColumn(onDelete="CASCADE")
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
     * Get id
     *
     * @return integer
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
