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

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Role;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_panel_facet_role")
 */
class PanelFacetRole
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_facet_admin"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="panelFacetsRole"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"api_facet_admin"})
     */
    protected $role;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\panelFacet",
     *     inversedBy="panelFacetsRole"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $panelFacet;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_facet_admin"})
     */
    protected $canOpen = false;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_facet_admin"})
     */
    protected $canEdit = false;

    public function getId()
    {
        return $this->id;
    }

    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setPanelFacet(PanelFacet $panelFacet)
    {
        $this->panelFacet = $panelFacet;
        $panelFacet->addPanelFacetRole($this);
    }

    public function getPanelFacet()
    {
        return $this->panelFacet;
    }

    public function setCanOpen($bool)
    {
        $this->canOpen = $bool;
    }

    public function canOpen()
    {
        return $this->canOpen;
    }

    public function setCanEdit($bool)
    {
        $this->canEdit = $bool;
    }

    public function canEdit()
    {
        return $this->canEdit;
    }
}
