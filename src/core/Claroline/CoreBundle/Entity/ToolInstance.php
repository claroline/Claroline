<?php

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Annotation\ORM as ORMExt;
use Claroline\CoreBundle\Entity\Workspace;
use Claroline\CoreBundle\Entity\Tool;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_tool_instance")
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
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Tool")
     * @ORM\JoinColumn(name="tool_id", referencedColumnName="id")
     */
    protected $toolType;
    
    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace")
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    protected $hostWorkspace;
    
    public function getId()
    {
        return $this->id;
    }
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
