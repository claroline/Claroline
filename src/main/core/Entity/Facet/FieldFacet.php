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
    const NUMBER_TYPE = 'number';
    /** @var int */
    const DATE_TYPE = 'date';
    /** @var int */
    const COUNTRY_TYPE = 'country';
    /** @var int */
    const EMAIL_TYPE = 'email';
    /** @var int */
    const HTML_TYPE = 'html';
    /** @var int */
    const CASCADE_TYPE = 'cascade';
    /** @var int */
    const FILE_TYPE = 'file';
    /** @var int */
    const BOOLEAN_TYPE = 'boolean';
    /** @var int */
    const CHOICE_TYPE = 'choice';

    /**
     * @ORM\Column(name="name")
     *
     * @var string
     */
    private $label;

    /**
     * @ORM\Column()
     *
     * @var string
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
    private $metadata = false;

    /**
     * @ORM\Column(name="locked", type="boolean", options={"default" = 0})
     */
    private $locked = false;

    /**
     * @ORM\Column(name="locked_edition", type="boolean", options={"default" = 0})
     */
    private $lockedEditionOnly = false;

    /**
     * @ORM\Column(name="help", nullable=true)
     */
    private $help;

    /**
     * @ORM\Column(name="condition_field", type="string", nullable=true)
     *
     * @var string
     */
    private $conditionField;

    /**
     * @ORM\Column(name="condition_comparator", type="string", nullable=true)
     *
     * @var string
     */
    private $conditionComparator;

    /**
     * @ORM\Column(name="condition_value", type="json", nullable=true)
     *
     * @var array
     */
    private $conditionValue = [];

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

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getType(): ?string
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

    public function setHelp(?string $help)
    {
        $this->help = $help;
    }

    public function getConditionField(): ?string
    {
        return $this->conditionField;
    }

    public function setConditionField(?string $conditionField)
    {
        $this->conditionField = $conditionField;
    }

    public function getConditionComparator(): ?string
    {
        return $this->conditionComparator;
    }

    public function setConditionComparator(?string $conditionComparator)
    {
        $this->conditionComparator = $conditionComparator;
    }

    public function getConditionValue()
    {
        return $this->conditionValue;
    }

    public function setConditionValue($conditionValue)
    {
        $this->conditionValue = $conditionValue;
    }
}
