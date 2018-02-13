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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Facet\FieldFacetRepository")
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
    private static $types = [
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
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="name")
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $label;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $type;

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
    private $panelFacet;

    /**
     * @ORM\Column(type="integer", name="position", nullable=true)
     *
     * @var int
     */
    private $position;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetChoice",
     *     mappedBy="fieldFacet"
     * )
     *
     * @var ArrayCollection
     */
    private $fieldFacetChoices;

    /**
     * @ORM\Column(name="isRequired", type="boolean")
     *
     * @var bool
     */
    private $required = false;

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
    private $resourceNode;

    /**
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    private $options = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
        $this->fieldsFacetValue = new ArrayCollection();
        $this->fieldFacetChoices = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return string
     */
    public function getName()
    {
        $string = str_replace(' ', '-', $this->label); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        $string = preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.

        return strtolower($string);
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param int|string $type
     */
    public function setType($type)
    {
        //if we pass a correct type name
        if (in_array($type, array_keys(static::$types))) {
            $this->type = static::$types[$type];
        } elseif (in_array($type, static::$types)) {
            //otherwise we use the integer
            $this->type = $type;
        } else {
            throw new \InvalidArgumentException(
                'Type must be a FieldFacet class constant'
            );
        }
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
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
     * @deprecated
     *
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
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
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
