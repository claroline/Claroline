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
 * FieldLabel.
 *
 * @ORM\Table(name="claro_fcbundle_field_label")
 * @ORM\Entity
 */
class FieldLabel
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_note_type",
     *     "api_flashcard_note",
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
     *     "api_flashcard_note_type",
     *     "api_flashcard_note",
     *     "api_flashcard_card",
     *     "api_flashcard_deck"
     * })
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="NoteType", inversedBy="fieldLabels")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $noteType;

    /**
     * @ORM\OneToMany(targetEntity="FieldValue", mappedBy="fieldLabel")
     */
    private $fieldValues;

    public function __construct()
    {
        $this->fieldValues = new ArrayCollection();
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
     * @return FieldLabel
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
     * @param NoteType $obj
     *
     * @return FieldLabel
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
    public function getFieldValues()
    {
        return $this->fieldValues;
    }
}
