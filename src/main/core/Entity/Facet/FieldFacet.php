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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Restriction\Hidden;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Facet\FieldFacetRepository")
 * @ORM\Table(name="claro_field_facet")
 */
class FieldFacet
{
    use Id;
    use Uuid;
    // Restrictions
    use Hidden;

    /** @var int */
    const STRING_TYPE = 1;
    /** @var int */
    const NUMBER_TYPE = 2;
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
    const HTML_TYPE = 9;
    /** @var int */
    const CASCADE_TYPE = 10;
    /** @var int */
    const FILE_TYPE = 11;
    /** @var int */
    const BOOLEAN_TYPE = 12;
    /** @var int */
    const CHOICE_TYPE = 13;
    /** @var array */
    public static $types = [
        'string' => self::STRING_TYPE,
        'number' => self::NUMBER_TYPE,
        'date' => self::DATE_TYPE,
        'country' => self::COUNTRY_TYPE,
        'email' => self::EMAIL_TYPE,
        'html' => self::HTML_TYPE,
        'cascade' => self::CASCADE_TYPE,
        'file' => self::FILE_TYPE,
        'boolean' => self::BOOLEAN_TYPE,
        'choice' => self::CHOICE_TYPE,
    ];

    /**
     * @ORM\Column(name="name")
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
    private $position = 0;

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
     * @ORM\Column(type="json")
     *
     * @var array
     */
    private $options = [];

    /**
     * @ORM\Column(name="is_metadata", type="boolean", options={"default" = 0})
     */
    protected $metadata = false;

    /**
     * @ORM\Column(name="locked", type="boolean", options={"default" = 0})
     */
    protected $locked = false;

    /**
     * @ORM\Column(name="locked_edition", type="boolean", options={"default" = 0})
     */
    protected $lockedEditionOnly = false;

    /**
     * @ORM\Column(name="help", nullable=true)
     */
    protected $help;

    public function __construct()
    {
        $this->refreshUuid();
        $this->fieldFacetChoices = new ArrayCollection();
    }

    public function setPanelFacet(?PanelFacet $panelFacet = null)
    {
        $this->panelFacet = $panelFacet;

        if ($panelFacet) {
            $panelFacet->addFieldFacet($this);
        }
    }

    public function getPanelFacet(): ?PanelFacet
    {
        return $this->panelFacet;
    }

    public function getName(): string
    {
        $string = str_replace(' ', '-', $this->label); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        $string = preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.

        return $this->id.'-'.strtolower($string);
    }

    public function setLabel(string $label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel(): ?string
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
            throw new \InvalidArgumentException('Type must be a FieldFacet class constant');
        }
    }

    public function getType(): ?int
    {
        return $this->type;
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

    public function setPosition(?int $position = null)
    {
        $this->position = $position;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getFieldType(): string
    {
        switch ($this->type) {
            case self::NUMBER_TYPE: return 'number';
            case self::DATE_TYPE: return 'date';
            case self::STRING_TYPE: return 'string';
            case self::COUNTRY_TYPE: return 'country';
            case self::EMAIL_TYPE: return 'email';
            case self::HTML_TYPE: return 'html';
            case self::CASCADE_TYPE: return 'cascade';
            case self::FILE_TYPE: return 'file';
            case self::BOOLEAN_TYPE: return 'boolean';
            case self::CHOICE_TYPE: return 'choice';
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
     * Get root choices.
     *
     * @return FieldFacetChoice[]
     */
    public function getRootFieldFacetChoices()
    {
        $roots = [];

        if (!empty($this->fieldFacetChoices)) {
            foreach ($this->fieldFacetChoices as $choice) {
                if (empty($choice->getParent())) {
                    $roots[] = $choice;
                }
            }
        }

        return $roots;
    }

    public function emptyFieldFacetChoices()
    {
        $this->fieldFacetChoices->clear();
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required)
    {
        $this->required = $required;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function isMetadata(): bool
    {
        return $this->metadata;
    }

    public function setMetadata(bool $isMetadata)
    {
        $this->metadata = $isMetadata;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked)
    {
        $this->locked = $locked;
    }

    public function getLockedEditionOnly(): bool
    {
        return $this->lockedEditionOnly;
    }

    public function setLockedEditionOnly(bool $lockedEditionOnly)
    {
        $this->lockedEditionOnly = $lockedEditionOnly;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setHelp(string $help)
    {
        $this->help = $help;
    }
}
