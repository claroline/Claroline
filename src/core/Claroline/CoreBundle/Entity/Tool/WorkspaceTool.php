<?php

namespace Claroline\CoreBundle\Entity\Tool;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_workspace_tools")
 */
class WorkspaceTool
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    private $workspace;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\Tool",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="tool_id", referencedColumnName="id")
     */
    private $tool;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\WorkspaceToolRole",
     *     mappedBy="workspaceTool"
     * )
     */
    private $workspaceToolRoles;

    public function __construct() {
        $this->workspaceToolRoles =  new ArrayCollection();
    }

    public function getId()
    {
        return $this->getId();
    }

    public function setTool($tool)
    {
        $this->tool = $tool;
    }

    public function getTool()
    {
        return $this->tool;
    }

    public function setWorkspace($ws)
    {
        $this->workspace = $ws;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function getWorkspaceToolRoles()
    {
        return $this->workspaceToolRoles;
    }
}
