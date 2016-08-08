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

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * FieldValue.
 *
 * @ORM\Table(name="claro_fcbundle_field_value")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type_discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "generic" = "FieldValue",
 *     "text" = "FieldValueText",
 *     "image" = "FieldValueImage"
 * })
 */
class FieldValue
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
    protected $id;

    /**
     * @var string
     *
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_note",
     *     "api_flashcard_card",
     *     "api_flashcard_deck"
     * })
     */
    protected $type = 'generic';

    /**
     * @var text
     *
     * @ORM\Column(name="value", type="text")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_note",
     *     "api_flashcard_card",
     *     "api_flashcard_deck"
     * })
     */
    protected $value;

    /**
     * @var text
     *
     * @ORM\Column(name="mimetype", type="string")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_note",
     *     "api_flashcard_card",
     *     "api_flashcard_deck"
     * })
     */
    protected $mimetype = '';

    /**
     * @ORM\ManyToOne(targetEntity="FieldLabel", inversedBy="fieldValues")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_note",
     *     "api_flashcard_card",
     *     "api_flashcard_deck"
     * })
     */
    protected $fieldLabel;

    /**
     * @ORM\ManyToOne(targetEntity="Note", inversedBy="fieldValues")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $note;

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
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $value
     *
     * @return FieldValue
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param FieldLabel $obj
     *
     * @return FieldValue
     */
    public function setFieldLabel(FieldLabel $obj)
    {
        $this->fieldLabel = $obj;

        return $this;
    }

    /**
     * @return FieldLabel
     */
    public function getFieldLabel()
    {
        return $this->fieldLabel;
    }

    /**
     * @param Note $obj
     *
     * @return FieldValue
     */
    public function setNote(Note $obj)
    {
        $this->note = $obj;

        return $this;
    }

    /**
     * @return Note
     */
    public function getNote()
    {
        return $this->note;
    }
}
