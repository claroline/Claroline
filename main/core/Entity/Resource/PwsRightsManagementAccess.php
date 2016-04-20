<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Role;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\PwsRightsManagementAccessRepository")
 * @ORM\Table(name="claro_personal_workspace_resource_rights_management_access")
 */
class PwsRightsManagementAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="pwsRightsManagementAccess"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $role;

    /**
     * @ORM\Column(name="is_accessible", type="boolean")
     */
    protected $isAccessible;

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

    public function setIsAccessible($bool)
    {
        $this->isAccessible = $bool;
    }

    public function isAccessible()
    {
        return $this->isAccessible;
    }

    public function getIsAccessible()
    {
        return $this->isAccessible;
    }
}
