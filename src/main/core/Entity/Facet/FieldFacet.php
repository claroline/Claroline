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

use Claroline\AppBundle\Entity\Display\Order;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Facet\FieldFacetRepository")
 *
 * @ORM\Table(name="claro_field_facet")
 */
class FieldFacet
{
    use Id;
    use Uuid;
    use Order;

    /** @var string */
    public const NUMBER_TYPE = 'number';
    /** @var string */
    public const DATE_TYPE = 'date';
    /** @var string */
    public const COUNTRY_TYPE = 'country';
    /** @var string */
    public const EMAIL_TYPE = 'email';
    public const TEXT_TYPE = 'string';
    /** @var string */
    public const HTML_TYPE = 'html';
    /** @var string */
    public const CASCADE_TYPE = 'cascade';
    /** @var string */
    public const FILE_TYPE = 'file';
    /** @var string */
    public const BOOLEAN_TYPE = 'boolean';
    /** @var string */
    public const CHOICE_TYPE = 'choice';

    /**
     * No confidentiality. All users which can open the parent entity see the field.
     */
    public const CONFIDENTIALITY_NONE = 'none';

    /**
     * Only the user which own the parent entity or
     * a user with the `administrate` right on the parent entity (eg. User, Training) can see the field.
     */
    public const CONFIDENTIALITY_OWNER = 'owner';

    /**
     * Only a user with the `administrate` right on the parent entity (eg. User, Training) can see the field.
     */
    public const CONFIDENTIALITY_MANAGER = 'manager';

    /**
     * @ORM\Column(name="name")
     */
    private ?string $label = null;

    /**
     * @ORM\Column()
     */
    private ?string $type = null;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Facet\PanelFacet",
     *      inversedBy="fieldsFacet"
     * )
     *
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     */
    private ?PanelFacet $panelFacet = null;

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
     */
    private bool $required = false;

    /**
     * @ORM\Column(type="json")
     */
    private array $options = [];

    /**
     * @ORM\Column(type="string")
     */
    private string $confidentiality = self::CONFIDENTIALITY_NONE;

    /**
     * @ORM\Column(name="locked", type="boolean", options={"default" = 0})
     */
    private bool $locked = false;

    /**
     * @ORM\Column(name="locked_edition", type="boolean", options={"default" = 0})
     */
    private bool $lockedEditionOnly = false;

    /**
     * @ORM\Column(name="help", nullable=true)
     */
    private ?string $help = null;

    /**
     * @ORM\Column(name="condition_field", type="string", nullable=true)
     */
    private ?string $conditionField = null;

    /**
     * @ORM\Column(name="condition_comparator", type="string", nullable=true)
     */
    private ?string $conditionComparator = null;

    /**
     * @ORM\Column(name="condition_value", type="json", nullable=true)
     */
    private mixed $conditionValue = null;

    /**
     * @ORM\Column(name="hide_label", type="boolean")
     */
    private bool $hideLabel = false;

    public function __construct()
    {
        $this->refreshUuid();

        $this->fieldFacetChoices = new ArrayCollection();
    }

    public function setPanelFacet(PanelFacet $panelFacet = null): void
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

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function addFieldChoice(FieldFacetChoice $choice): void
    {
        if (!$this->fieldFacetChoices->contains($choice)) {
            $this->fieldFacetChoices->add($choice);
        }
    }

    public function removeFieldChoice(FieldFacetChoice $choice): void
    {
        if ($this->fieldFacetChoices->contains($choice)) {
            $this->fieldFacetChoices->removeElement($choice);
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
    public function getRootFieldFacetChoices(): array
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

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    public function getOptions(): ?array
    {
        return $this->options ?? [];
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getConfidentiality(): string
    {
        return $this->confidentiality;
    }

    public function setConfidentiality(string $confidentiality): void
    {
        $this->confidentiality = $confidentiality;
    }

    /**
     * @deprecated
     */
    public function isMetadata(): bool
    {
        return self::CONFIDENTIALITY_NONE !== $this->confidentiality;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): void
    {
        $this->locked = $locked;
    }

    public function getLockedEditionOnly(): bool
    {
        return $this->lockedEditionOnly;
    }

    public function setLockedEditionOnly(bool $lockedEditionOnly): void
    {
        $this->lockedEditionOnly = $lockedEditionOnly;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setHelp(?string $help): void
    {
        $this->help = $help;
    }

    public function getConditionField(): ?string
    {
        return $this->conditionField;
    }

    public function setConditionField(?string $conditionField): void
    {
        $this->conditionField = $conditionField;
    }

    public function getConditionComparator(): ?string
    {
        return $this->conditionComparator;
    }

    public function setConditionComparator(?string $conditionComparator): void
    {
        $this->conditionComparator = $conditionComparator;
    }

    public function getConditionValue(): mixed
    {
        return $this->conditionValue;
    }

    public function setConditionValue($conditionValue): void
    {
        $this->conditionValue = $conditionValue;
    }

    public function getHideLabel(): bool
    {
        return $this->hideLabel;
    }

    public function setHideLabel(bool $hideLabel): void
    {
        $this->hideLabel = $hideLabel;
    }
}
