<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Tool;

use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Tool\AdministrationToolRepository")
 * @ORM\Table(
 *      name="claro_admin_tools",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="admin_tool_plugin_unique",columns={"name", "plugin_id"})}
 * )
 *
 * @todo merge with Tool entity
 */
class AdminTool extends AbstractTool
{
    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="adminTools"
     * )
     * @ORM\JoinTable(name="claro_admin_tool_role")
     *
     * @var Role[]|ArrayCollection
     */
    protected $roles;

    /**
     * AdminTool constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->roles = new ArrayCollection();
    }

    /**
     * @param Role $role
     */
    public function addRole(Role $role)
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    /**
     * @param Role $role
     */
    public function removeRole(Role $role)
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }
    }

    /**
     * @return Role[]|ArrayCollection
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
