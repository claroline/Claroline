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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
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
    use Id;
    use Uuid;

    const FORCED = 'forced';
    const HIDDEN = 'hidden';

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
     * @ORM\Column(name="display", nullable=true)
     *
     * @var string
     */
    protected $display;

    /**
     * @ORM\Column(name="tool_order", type="integer", nullable=true)
     *
     * @var int
     */
    protected $order;

    /**
     * ToolRole constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
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
     * @return string
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * @param string $display
     */
    public function setDisplay($display)
    {
        $this->display = $display;
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
