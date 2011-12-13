<?php

namespace Claroline\PluginBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CommonBundle\Library\Annotation as ORMExt;
use Claroline\WorkspaceBundle\Entity\Workspace;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_tool_instance")
 * @ORMExt\Extendable(discriminatorColumn="discr")
 */
class ToolInstance
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\OneToOne(targetEntity="Claroline\PluginBundle\Entity\Tool")
     * @ORM\JoinColumn(name="tool_id", referencedColumnName="id")
     */
    protected $toolType;
    
    /**
     * @ORM\OneToOne(targetEntity="Claroline\WorkspaceBundle\Entity\Workspace")
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    protected $hostWorkspace;
    
    public function getToolType()
    {
        return $this->toolType;
    }

    public function setToolType(Tool $toolType)
    {
        $this->toolType = $toolType;
    }

    public function getHostWorkspace()
    {
        return $this->hostWorkspace;
    }

    public function setHostWorkspace(Workspace $hostWorkspace)
    {
        $this->hostWorkspace = $hostWorkspace;
    }
}