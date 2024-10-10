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
use Claroline\AppBundle\Entity\Meta\Name;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_facet')]
#[ORM\Entity]
class Facet
{
    use Id;
    use Uuid;
    use Name;
    use Order;
    use Icon;

    #[ORM\Column(name: 'isMain', type: Types::BOOLEAN)]
    private bool $main = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $forceCreationForm = false;

    /**
     * @var Collection<int, PanelFacet>
     */
    #[ORM\OneToMany(targetEntity: PanelFacet::class, mappedBy: 'facet', cascade: ['all'])]
    #[ORM\OrderBy(['order' => 'ASC'])]
    private Collection $panelFacets;

    public function __construct()
    {
        $this->refreshUuid();

        $this->panelFacets = new ArrayCollection();
    }

    public function isMain(): bool
    {
        return $this->main;
    }

    public function setMain(bool $main): void
    {
        $this->main = $main;
    }

    public function getForceCreationForm(): bool
    {
        return $this->forceCreationForm;
    }

    public function setForceCreationForm(bool $forceCreationForm): void
    {
        $this->forceCreationForm = $forceCreationForm;
    }

    public function addPanelFacet(PanelFacet $panelFacet): void
    {
        if (!$this->panelFacets->contains($panelFacet)) {
            $this->panelFacets->add($panelFacet);
        }
    }

    public function removePanelFacet(PanelFacet $panelFacet): void
    {
        if ($this->panelFacets->contains($panelFacet)) {
            $this->panelFacets->removeElement($panelFacet);
        }
    }

    public function getPanelFacets(): Collection
    {
        return $this->panelFacets;
    }

    public function resetPanelFacets(): void
    {
        foreach ($this->panelFacets as $panelFacet) {
            $panelFacet->setFacet(null);
        }

        $this->panelFacets = new ArrayCollection();
    }
}
