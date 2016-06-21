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
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\PwsToolConfigRepository")
 * @ORM\Table(
 *     name="claro_personnal_workspace_tool_config",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="pws_unique_tool_config",
 *             columns={"tool_id", "role_id"}
 *         )
 *     }
 * )
 */
class PwsToolConfig
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
     *     inversedBy="pwsToolConfig"
     * )
     * @ORM\JoinColumn(name="role_id", nullable=false, onDelete="CASCADE")
     */
    protected $role;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\Tool",
     *     inversedBy="pwsToolConfig",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="tool_id", nullable=false, onDelete="CASCADE")
     */
    protected $tool;

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

    public function getTool()
    {
        return $this->tool;
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

    public function setTool(Tool $tool)
    {
        $this->tool = $tool;
    }
}
