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
use Claroline\CoreBundle\Entity\Role;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_panel_facet_role")
 */
class PanelFacetRole
{
    use UuidTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Role")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var Role
     */
    protected $role;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\PanelFacet",
     *     inversedBy="panelFacetsRole"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var PanelFacet
     */
    protected $panelFacet;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $canOpen = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $canEdit = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Role $role
     */
    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    /**
     * return Role.
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param PanelFacet $panelFacet
     */
    public function setPanelFacet(PanelFacet $panelFacet)
    {
        $this->panelFacet = $panelFacet;
        $panelFacet->addPanelFacetRole($this);
    }

    /**
     * @return PanelFacet
     */
    public function getPanelFacet()
    {
        return $this->panelFacet;
    }

    /**
     * @param bool $bool
     */
    public function setCanOpen($bool)
    {
        $this->canOpen = $bool;
    }

    /**
     * @return bool
     */
    public function canOpen()
    {
        return $this->canOpen;
    }

    /**
     * @param bool $bool
     */
    public function setCanEdit($bool)
    {
        $this->canEdit = $bool;
    }

    /**
     * @return bool
     */
    public function canEdit()
    {
        return $this->canEdit;
    }
}
