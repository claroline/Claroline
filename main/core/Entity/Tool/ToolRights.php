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
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ToolRightsRepository")
 * @ORM\Table(
 *     name="claro_tool_rights",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="tool_rights_unique_ordered_tool_role",
 *             columns={"ordered_tool_id", "role_id"}
 *         )
 *     }
 * )
 */
class ToolRights
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $mask = 0;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="toolRights"
     * )
     * @ORM\JoinColumn(name="role_id", nullable=false, onDelete="CASCADE")
     */
    protected $role;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\OrderedTool",
     *     inversedBy="rights",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="ordered_tool_id", nullable=false, onDelete="CASCADE")
     */
    protected $orderedTool;

    public function getId()
    {
        return $this->id;
    }

    public function getMask()
    {
        return $this->mask;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getOrderedTool()
    {
        return $this->orderedTool;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setMask($mask)
    {
        $this->mask = $mask;
    }

    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    public function setOrderedTool(OrderedTool $orderedTool)
    {
        $this->orderedTool = $orderedTool;
    }
}
