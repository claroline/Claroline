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

use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_log_connect_tool")
 */
class LogConnectTool extends AbstractLogConnect
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Tool\OrderedTool")
     * @ORM\JoinColumn(name="tool_id", onDelete="SET NULL", nullable=true)
     */
    protected $tool;

    /**
     * @ORM\Column(name="tool_name")
     */
    protected $toolName;

    /**
     * @ORM\Column(name="original_tool_name")
     */
    protected $originalToolName;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(name="workspace_id", onDelete="SET NULL", nullable=true)
     */
    protected $workspace;

    /**
     * @ORM\Column(name="workspace_name", nullable=true)
     */
    protected $workspaceName;

    /**
     * @return OrderedTool
     */
    public function getTool()
    {
        return $this->tool;
    }

    /**
     * @param OrderedTool $tool
     */
    public function setTool(OrderedTool $tool = null)
    {
        $this->tool = $tool;

        if ($tool) {
            $this->setToolName($tool->getTool()->getName());
            $this->setOrignalToolName($tool->getTool()->getName());

            $workspace = $tool->getWorkspace();

            if ($workspace) {
                $this->setWorkspace($workspace);
                $this->setWorkspaceName($tool->getWorkspace()->getName());
            }
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

    /**
     * @return string
     */
    public function getOrignalToolName()
    {
        return $this->originalToolName;
    }

    /**
     * @param string $originalToolName
     */
    public function setOrignalToolName($originalToolName)
    {
        $this->originalToolName = $originalToolName;
    }

    /**
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * @param Workspace $workspace
     */
    public function setWorkspace(Workspace $workspace = null)
    {
        $this->workspace = $workspace;

        if ($workspace) {
            $this->setWorkspaceName($workspace->getName());
        }
    }

    /**
     * @return string
     */
    public function getWorkspaceName()
    {
        return $this->workspaceName;
    }

    /**
     * @param string $workspaceName
     */
    public function setWorkspaceName($workspaceName)
    {
        $this->workspaceName = $workspaceName;
    }
}
