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
 * @ORM\Table(name="claro_fcbundle_note_type")
 * @ORM\Entity
 */
class NoteType
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_note_type",
     *     "api_flashcard_note",
     *     "api_flashcard_deck"
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_note_type",
     *     "api_flashcard_note",
     *     "api_flashcard_deck"
     * })
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="FieldLabel", mappedBy="noteType")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_note_type",
     *     "api_flashcard_note",
     *     "api_flashcard_deck"
     * })
     */
    private $fieldLabels;

    /**
     * @ORM\OneToMany(targetEntity="CardType", mappedBy="noteType")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_note_type"
     * })
     */
    private $cardTypes;

    /**
     * @ORM\OneToMany(targetEntity="Note", mappedBy="noteType")
     */
    private $notes;

    public function __construct()
    {
        $this->fieldLabels = new ArrayCollection();
        $this->cardTypes = new ArrayCollection();
        $this->notes = new ArrayCollection();
    }

    /**
     * Check if the structure of this object is correct.
     *
     * @return bool
     */
    public function isValid()
    {
        if ($this->fieldLabels->count() < 2) {
            return false;
        }
        foreach ($this->cardTypes as $cardType) {
            if ($cardType->getQuestions()->count() < 1) {
                return false;
            }
            if ($cardType->getAnswers()->count() < 1) {
                return false;
            }
            foreach ($cardType->getQuestions() as $q) {
                if (!$this->fieldLabels->contains($q)) {
                    return false;
                }
            }
            foreach ($cardType->getAnswers() as $a) {
                if (!$this->fieldLabels->contains($a)) {
                    return false;
                }
            }
        }

        return true;
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
     * @return NoteType
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
    public function addFieldLabel(FieldLabel $obj)
    {
        if ($this->fieldLabels->contains($obj)) {
            return false;
        }

        return $this->fieldLabels->add($obj);
    }

    /**
     * @param ArrayCollection $obj
     *
     * @return NoteType
     */
    public function setFieldLabels(ArrayCollection $obj)
    {
        $this->fieldLabels = $obj;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getFieldLabels()
    {
        return $this->fieldLabels;
    }

    /**
     * @param int $id
     *
     * @return FieldLabel Null if the field is not found
     */
    public function getFieldLabel($id)
    {
        foreach ($this->fieldLabels as $fieldLabel) {
            if ($fieldLabel->getId() === $id) {
                return $fieldLabel;
            }
        }
    }

    /**
     * @param string $name
     *
     * @return FieldLabel Null if the field is notfound
     */
    public function getFieldLabelFromName($name)
    {
        foreach ($this->fieldLabels as $f) {
            if (strcmp($f->getName(), $name) === 0) {
                return $f;
            }
        }
    }

    /**
     * @param CardType
     *
     * @return bool
     */
    public function addCardType(CardType $obj)
    {
        if ($this->fieldLabels->contains($obj)) {
            return false;
        }

        return $this->cardTypes->add($obj);
    }

    /**
     * @param ArrayCollection $obj
     *
     * @return NoteType
     */
    public function setCardTypes(ArrayCollection $obj)
    {
        $this->cardTypes = $obj;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getCardTypes()
    {
        return $this->cardTypes;
    }

    /**
     * @return ArrayCollection
     */
    public function getNotes()
    {
        return $this->notes;
    }
}
