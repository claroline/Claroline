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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_field_facet_choice')]
#[ORM\Entity]
class FieldFacetChoice
{
    use Id;
    use Uuid;

    #[ORM\Column]
    private ?string $name = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: FieldFacet::class, inversedBy: 'fieldFacetChoices')]
    private ?FieldFacet $fieldFacet = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $position = 0;

    #[ORM\ManyToOne(targetEntity: FieldFacetChoice::class, inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?FieldFacetChoice $parent = null;

    /**
     * @var Collection<int, FieldFacetChoice>
     */
    #[ORM\OneToMany(targetEntity: FieldFacetChoice::class, mappedBy: 'parent', cascade: ['persist', 'remove'])]
    private Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->refreshUuid();
    }

    public function setLabel(string $label): void
    {
        $this->name = $label;
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function setFieldFacet(FieldFacet $ff): void
    {
        $this->fieldFacet = $ff;
        $ff->addFieldChoice($this);
    }

    public function getFieldFacet(): ?FieldFacet
    {
        return $this->fieldFacet;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getParent(): ?FieldFacetChoice
    {
        return $this->parent;
    }

    public function setParent(self $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getChildren(): array
    {
        return $this->children->toArray();
    }

    public function addChild(FieldFacetChoice $child): void
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
        }
    }

    public function removeChild(FieldFacetChoice $child): void
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
        }
    }

    /**
     * for the api form select field.
     */
    public function getValue(): ?string
    {
        return $this->name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
