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

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="claro_tools_role",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="tool_role_unique",columns={"tool_id", "role_id"})}
 * )
 */
class ToolRole
{
    use UuidTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Tool\Tool")
     * @ORM\JoinColumn(name="tool_id", nullable=false, onDelete="CASCADE")
     *
     * @var Tool
     */
    protected $tool;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Role")
     * @ORM\JoinColumn(name="role_id", nullable=false, onDelete="CASCADE")
     *
     * @var Role
     */
    protected $role;

    /**
     * @ORM\Column(name="visible", type="boolean")
     */
    protected $visible = false;

    /**
     * @ORM\Column(name="locked", type="boolean")
     */
    protected $locked = false;

    /**
     * @ORM\Column(name="tool_order", type="integer", nullable=true)
     */
    protected $order;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Tool
     */
    public function getTool()
    {
        return $this->tool;
    }

    /**
     * @param Tool $tool
     */
    public function setTool(Tool $tool)
    {
        $this->tool = $tool;
    }

    /**
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param Role $role
     */
    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @param bool $locked
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    /**
     * @return int|null
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int|null $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }
}
