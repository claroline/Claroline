<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Facet;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Accessor;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\FieldFacetRepository")
 * @ORM\Table(name="claro_field_facet")
 */
class FieldFacet
{
    const STRING_TYPE = 1;
    const FLOAT_TYPE = 2;
    const DATE_TYPE = 3;
    const RADIO_TYPE = 4;
    const SELECT_TYPE = 5;
    const CHECKBOXES_TYPE = 6;
    const COUNTRY_TYPE = 7;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $id;

    /**
     * @ORM\Column
     * @Assert\NotBlank()
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $name;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $type;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Facet\PanelFacet",
     *      inversedBy="fieldsFacet"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    protected $panelFacet;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetValue",
     *     mappedBy="fieldFacet",
     *     cascade={"persist"}
     * )
     */
    protected $fieldsFacetValue;

    /**
     * @ORM\Column(type="integer", name="position")
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $position;

    /**
     * @Groups({"api_facet_admin", "api_profile"})
     * @Accessor(getter="getInputType")
     */
    protected $translationKey;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetChoice",
     *     mappedBy="fieldFacet"
     * )
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $fieldFacetChoices;

    /**
     * @Groups({"api_profile"})
     * @Accessor(getter="getUserFieldValue")
     */
    protected $userFieldValue;

    /**
     * @Groups({"api_profile"})
     * @Accessor(getter="isEditable")
     */
    protected $isEditable;

    public function __construct()
    {
        $this->fieldsFacetValue = new ArrayCollection();
        $this->fieldFacetChoices = new ArrayCollection();
    }

    /**
     * @param Facet $facet
     */
    public function setPanelFacet(PanelFacet $panelFacet)
    {
        $panelFacet->addFieldFacet($this);
        $this->panelFacet = $panelFacet;
    }

    /**
     * @return Facet
     */
    public function getPanelFacet()
    {
        return $this->panelFacet;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getFieldsFacetValue()
    {
        return $this->fieldsFacetValue;
    }

    public function addFieldFacet(FieldFacetValue $fieldFacetValue)
    {
        $this->fieldsFacetValue->add($fieldFacetValue);
    }

    public function addFieldChoice(FieldFacetChoice $choice)
    {
        $this->fieldFacetChoices->add($choice);
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getTypeTranslationKey()
    {
        switch ($this->type) {
            case self::FLOAT_TYPE: return 'number';
            case self::DATE_TYPE: return 'date';
            case self::STRING_TYPE: return 'text';
            case self::RADIO_TYPE: return 'radio';
            case self::SELECT_TYPE: return 'select';
            case self::CHECKBOXES_TYPE: return 'checkbox';
            case self::COUNTRY_TYPE: return 'country';
            default: return 'error';
        }
    }

    public function getInputType()
    {
        switch ($this->type) {
            case self::FLOAT_TYPE: return 'number';
            case self::DATE_TYPE: return 'date';
            case self::STRING_TYPE: return 'text';
            case self::RADIO_TYPE: return 'radio';
            case self::SELECT_TYPE: return 'select';
            case self::CHECKBOXES_TYPE: return 'checkbox';
            case self::COUNTRY_TYPE: return 'country';
            default: return 'error';
        }
    }

    public function getFieldFacetChoices()
    {
        return $this->fieldFacetChoices;
    }

    /**
     * For serialization in user profile. It's easier that way.
     */
    public function setUserFieldValue(FieldFacetValue $val)
    {
        $this->userFieldValue = $val;
    }

    /**
     * For serialization in user profile. It's easier that way.
     */
    public function getUserFieldValue()
    {
        return $this->userFieldValue ? $this->userFieldValue->getValue() : null;
    }

    /**
     * For serialization in user profile. It's easier that way.
     */
    public function setIsEditable($boolean)
    {
        $this->isEditable = $boolean;
    }

    /**
     * For serialization in user profile. It's easier that way.
     */
    public function isEditable()
    {
        return $this->isEditable;
    }

    public function getPrettyName()
    {
        $string = str_replace(' ', '-', $this->name); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        $string = preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.

        return strtolower($string);
    }
}
