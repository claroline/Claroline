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

use Claroline\AppBundle\Entity\Display\Icon;
use Claroline\AppBundle\Entity\Display\Order;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Name;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_panel_facet')]
#[ORM\Entity]
class PanelFacet
{
    use Id;
    use Uuid;
    use Name;
    use Description;
    use Icon;
    use Order;

    #[ORM\Column(name: 'help', nullable: true)]
    private ?string $help = null;

    /**
     * @todo : to remove. Only used in profile
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Facet::class, inversedBy: 'panelFacets')]
    private ?Facet $facet = null;

    /**
     * @var Collection<int, FieldFacet>
     */
    #[ORM\OneToMany(targetEntity: FieldFacet::class, mappedBy: 'panelFacet', cascade: ['all'])]
    #[ORM\OrderBy(['order' => 'ASC'])]
    private Collection $fieldsFacet;

    public function __construct()
    {
        $this->refreshUuid();

        $this->fieldsFacet = new ArrayCollection();
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setHelp(?string $help): void
    {
        $this->help = $help;
    }

    public function getFacet(): ?Facet
    {
        return $this->facet;
    }

    public function setFacet(?Facet $facet = null): void
    {
        $this->facet = $facet;

        if ($facet) {
            $facet->addPanelFacet($this);
        }
    }

    public function getFieldsFacet(): Collection
    {
        return $this->fieldsFacet;
    }

    public function addFieldFacet(FieldFacet $fieldFacet): void
    {
        if (!$this->fieldsFacet->contains($fieldFacet)) {
            $this->fieldsFacet->add($fieldFacet);
        }
    }

    public function removeFieldFacet(FieldFacet $fieldFacet): void
    {
        if ($this->fieldsFacet->contains($fieldFacet)) {
            $this->fieldsFacet->removeElement($fieldFacet);
        }
    }

    /**
     * Remove all field facets.
     */
    public function resetFieldFacets(): void
    {
        foreach ($this->fieldsFacet as $field) {
            $field->setPanelFacet(null);
        }

        $this->fieldsFacet = new ArrayCollection();
    }
}
