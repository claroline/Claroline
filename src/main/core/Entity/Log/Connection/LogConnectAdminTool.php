<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Log\Connection;

use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_log_connect_admin_tool")
 */
class LogConnectAdminTool extends AbstractLogConnect
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Tool\AdminTool")
     * @ORM\JoinColumn(name="tool_id", onDelete="SET NULL", nullable=true)
     */
    protected $tool;

    /**
     * @ORM\Column(name="tool_name")
     */
    protected $toolName;

    /**
     * @return AdminTool
     */
    public function getTool()
    {
        return $this->tool;
    }

    /**
     * @param AdminTool $tool
     */
    public function setTool(AdminTool $tool = null)
    {
        $this->tool = $tool;

        if ($tool) {
            $this->setToolName($tool->getName());
        }
    }

    /**
     * @return string
     */
    public function getToolName()
    {
        return $this->toolName;
    }

    /**
     * @param string $toolName
     */
    public function setToolName($toolName)
    {
        $this->toolName = $toolName;
    }
}
