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

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

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
    const EMAIL_TYPE = 8;
    const RICH_TEXT_TYPE = 9;
    const CASCADE_SELECT_TYPE = 10;
    const FILE_TYPE = 11;

    protected static $types = [
        self::STRING_TYPE,
        self::FLOAT_TYPE,
        self::DATE_TYPE,
        self::RADIO_TYPE,
        self::SELECT_TYPE,
        self::CHECKBOXES_TYPE,
        self::COUNTRY_TYPE,
        self::EMAIL_TYPE,
        self::RICH_TEXT_TYPE,
        self::CASCADE_SELECT_TYPE,
        self::FILE_TYPE,
    ];

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_facet_admin", "api_profile", "api_user_min"})
     */
    protected $id;

    /**
     * @ORM\Column
     * @Assert\NotBlank()
     * @Groups({"api_facet_admin", "api_profile", "api_user_min"})
     */
    protected $name;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"api_facet_admin", "api_profile", "api_user_min"})
     */
    protected $type;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Facet\PanelFacet",
     *      inversedBy="fieldsFacet"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
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
     * @ORM\Column(type="integer", name="position", nullable=true)
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $position;

    /**
     * @Groups({"api_facet_admin", "api_profile", "api_user_min"})
     * @Accessor(getter="getInputType")
     */
    protected $translationKey;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetChoice",
     *     mappedBy="fieldFacet"
     * )
     * @Groups({"api_facet_admin", "api_profile"})
     * @ORM\OrderBy({"position" = "ASC"})
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

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_profile", "api_facet_admin"})
     */
    protected $isRequired = false;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     inversedBy="fields"
     * )
     * @ORM\JoinColumn(name="resource_node", onDelete="CASCADE", nullable=true)
     */
    protected $resourceNode;

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
        if (!in_array($type, static::$types)) {
            throw new \InvalidArgumentException(
                'Type must be a FieldFacet class constant'
            );
        }

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
        if (!$this->fieldFacetChoices->contains($choice)) {
            $this->fieldFacetChoices->add($choice);
        }
    }

    public function removeFieldChoice(FieldFacetChoice $choice)
    {
        if ($this->fieldFacetChoices->contains($choice)) {
            $this->fieldFacetChoices->removeElement($choice);
        }
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
            case self::EMAIL_TYPE: return 'email';
            case self::RICH_TEXT_TYPE: return 'rich_text';
            case self::CASCADE_SELECT_TYPE: return 'cascade_select';
            case self::FILE_TYPE: return 'file';
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
            case self::CHECKBOXES_TYPE: return 'checkboxes';
            case self::COUNTRY_TYPE: return 'country';
            case self::EMAIL_TYPE: return 'email';
            case self::RICH_TEXT_TYPE: return 'rich_text';
            case self::CASCADE_SELECT_TYPE: return 'cascade_select';
            case self::FILE_TYPE: return 'file';
            default: return 'error';
        }
    }

    public function getFieldFacetChoices()
    {
        return $this->fieldFacetChoices;
    }

    public function getFieldFacetChoicesArray()
    {
        return $this->fieldFacetChoices->toArray();
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

    public function isRequired()
    {
        return $this->isRequired;
    }

    public function setIsRequired($isRequired)
    {
        $this->isRequired = $isRequired;
    }

    public function getPrettyName()
    {
        $string = str_replace(' ', '-', $this->name); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        $string = preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.

        return strtolower($string);
    }

    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    public function setResourceNode(ResourceNode $resourceNode = null)
    {
        $this->resourceNode = $resourceNode;
    }
}
