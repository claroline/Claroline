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

use Claroline\CoreBundle\Entity\Model\UuidTrait;
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
    use UuidTrait;

    /** @var int */
    const STRING_TYPE = 1;
    /** @var int */
    const FLOAT_TYPE = 2;
    /** @var int */
    const DATE_TYPE = 3;
    /** @var int */
    const RADIO_TYPE = 4;
    /** @var int */
    const SELECT_TYPE = 5;
    /** @var int */
    const CHECKBOXES_TYPE = 6;
    /** @var int */
    const COUNTRY_TYPE = 7;
    /** @var int */
    const EMAIL_TYPE = 8;
    /** @var int */
    const RICH_TEXT_TYPE = 9;
    /** @var int */
    const CASCADE_SELECT_TYPE = 10;
    /** @var int */
    const FILE_TYPE = 11;
    /** @var array */
    protected static $types = [
        'string' => self::STRING_TYPE,
        'float' => self::FLOAT_TYPE,
        'date' => self::DATE_TYPE,
        'radio' => self::RADIO_TYPE,
        'select' => self::SELECT_TYPE,
        'checkboxes' => self::CHECKBOXES_TYPE,
        'country' => self::COUNTRY_TYPE,
        'email' => self::EMAIL_TYPE,
        'text' => self::RICH_TEXT_TYPE,
        'cascade ' => self::CASCADE_SELECT_TYPE,
        'file' => self::FILE_TYPE,
    ];

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_facet_admin", "api_profile", "api_user_min"})
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column
     * @Assert\NotBlank()
     * @Groups({"api_facet_admin", "api_profile", "api_user_min"})
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"api_facet_admin", "api_profile", "api_user_min"})
     *
     * @var int
     */
    protected $type;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Facet\PanelFacet",
     *      inversedBy="fieldsFacet"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     *
     * @var PanelFacet
     *
     * @todo should not be declared here (not used in ClacoForm)
     */
    protected $panelFacet;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetValue",
     *     mappedBy="fieldFacet",
     *     cascade={"persist"}
     * )
     *
     * @var ArrayCollection
     */
    protected $fieldsFacetValue;

    /**
     * @ORM\Column(type="integer", name="position", nullable=true)
     * @Groups({"api_facet_admin", "api_profile"})
     *
     * @var int
     */
    protected $position;

    /**
     * @Groups({"api_facet_admin", "api_profile", "api_user_min"})
     * @Accessor(getter="getInputType")
     *
     * @var string
     */
    protected $translationKey;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetChoice",
     *     mappedBy="fieldFacet"
     * )
     * @Groups({"api_facet_admin", "api_profile"})
     * @ORM\OrderBy({"position" = "ASC"})
     *
     * @var ArrayCollection
     */
    protected $fieldFacetChoices;

    /**
     * @Groups({"api_profile"})
     * @Accessor(getter="getUserFieldValue")
     *
     * @var mixed
     */
    protected $userFieldValue;

    /**
     * @Groups({"api_profile"})
     * @Accessor(getter="isEditable")
     *
     * @var bool
     */
    protected $isEditable;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_profile", "api_facet_admin"})
     *
     * @var bool
     */
    protected $isRequired = false;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     inversedBy="fields"
     * )
     * @ORM\JoinColumn(name="resource_node", onDelete="CASCADE", nullable=true)
     *
     * @var ResourceNode
     *
     * @todo should not be declared here (not used in Profile)
     */
    protected $resourceNode;

    /**
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    protected $options = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->fieldsFacetValue = new ArrayCollection();
        $this->fieldFacetChoices = new ArrayCollection();
        $this->refreshUuid();
    }

    /**
     * @param PanelFacet|null $panelFacet
     */
    public function setPanelFacet(PanelFacet $panelFacet = null)
    {
        $this->panelFacet = $panelFacet;

        if ($panelFacet) {
            $panelFacet->addFieldFacet($this);
        }
    }

    /**
     * @return PanelFacet
     */
    public function getPanelFacet()
    {
        return $this->panelFacet;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int|string $type
     */
    public function setType($type)
    {
        //if we pass a correct type name
        if (in_array($type, array_keys(static::$types))) {
            $this->type = static::$types[$type];

            return $this;
        }

        //otherwise we use the integer
        if (!in_array($type, static::$types)) {
            throw new \InvalidArgumentException(
                'Type must be a FieldFacet class constant'
            );
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return ArrayCollection
     */
    public function getFieldsFacetValue()
    {
        return $this->fieldsFacetValue;
    }

    /**
     * @param FieldFacetValue $fieldFacetValue
     */
    public function addFieldFacet(FieldFacetValue $fieldFacetValue)
    {
        $this->fieldsFacetValue->add($fieldFacetValue);
    }

    /**
     * @param FieldFacetChoice $choice
     */
    public function addFieldChoice(FieldFacetChoice $choice)
    {
        if (!$this->fieldFacetChoices->contains($choice)) {
            $this->fieldFacetChoices->add($choice);
        }
    }

    /**
     * @param FieldFacetChoice $choice
     */
    public function removeFieldChoice(FieldFacetChoice $choice)
    {
        if ($this->fieldFacetChoices->contains($choice)) {
            $this->fieldFacetChoices->removeElement($choice);
        }
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
    public function getFieldType()
    {
        switch ($this->type) {
            case self::FLOAT_TYPE: return 'float';
            case self::DATE_TYPE: return 'date';
            case self::STRING_TYPE: return 'string';
            case self::RADIO_TYPE: return 'radio';
            case self::SELECT_TYPE: return 'select';
            case self::CHECKBOXES_TYPE: return 'checkboxes';
            case self::COUNTRY_TYPE: return 'country';
            case self::EMAIL_TYPE: return 'email';
            case self::RICH_TEXT_TYPE: return 'html';
            case self::CASCADE_SELECT_TYPE: return 'cascade';
            case self::FILE_TYPE: return 'file';
            default: return 'error';
        }
    }
    /**
     * @deprecated
     *
     * @return string
     */
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

    /**
     * @return ArrayCollection
     */
    public function getFieldFacetChoices()
    {
        return $this->fieldFacetChoices;
    }

    /**
     * @return array
     */
    public function getFieldFacetChoicesArray()
    {
        return $this->fieldFacetChoices->toArray();
    }

    /**
     * For serialization in user profile. It's easier that way.
     * note for myself: do we need to remove it ?
     *
     * @param FieldFacetValue $val
     */
    public function setUserFieldValue(FieldFacetValue $val)
    {
        $this->userFieldValue = $val;
    }

    /**
     * For serialization in user profile. It's easier that way.
     * note for myself: do we need to remove it ?
     *
     * @return FieldFacetValue|null
     */
    public function getUserFieldValue()
    {
        return $this->userFieldValue ? $this->userFieldValue->getValue() : null;
    }

    /**
     * For serialization in user profile. It's easier that way.
     *
     * @param bool $boolean
     *                      note for myself: do we need to remove it ?
     */
    public function setIsEditable($boolean)
    {
        $this->isEditable = $boolean;
    }

    /**
     * For serialization in user profile. It's easier that way.
     * note for myself: do we need to remove it ?
     *
     * @return bool
     */
    public function isEditable()
    {
        return $this->isEditable;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->isRequired;
    }

    /**
     * @param bool $isRequired
     */
    public function setIsRequired($isRequired)
    {
        $this->isRequired = $isRequired;
    }

    /**
     * @param bool $isRequired
     *
     * alias of setRequired
     */
    public function setRequired($isRequired)
    {
        $this->setIsRequired($isRequired);
    }

    /**
     * @return string
     */
    public function getPrettyName()
    {
        $string = str_replace(' ', '-', $this->name); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        $string = preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.

        return strtolower($string);
    }

    /**
     * @return ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    /**
     * @param ResourceNode|null $resourceNode
     */
    public function setResourceNode(ResourceNode $resourceNode = null)
    {
        $this->resourceNode = $resourceNode;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }
}
