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
 * @ORM\Table(name="claro_fcbundle_note_type")
 * @ORM\Entity
 */
class NoteType
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="FieldLabel", mappedBy="noteType")
     */
    private $fieldLabels;

    /**
     * @ORM\OneToMany(targetEntity="CardType", mappedBy="noteType")
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
     * @return boolean
     */
    public function addFieldLabel(FieldLabel $obj)
    {
        if($this->fieldLabels->contains($obj))
        {
            return false;
        }
        else
        {
            return $this->fieldLabels->add($obj);
        }
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
     * @param CardType
     *
     * @return boolean
     */
    public function addCardType(CardType $obj)
    {
        if($this->fieldLabels->contains($obj))
        {
            return false;
        }
        else
        {
            return $this->cardTypes->add($obj);
        }
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
