<?php

namespace Claroline\CoreBundle\Entity\Tool;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_workspace_tools_role")
 */
class WorkspaceToolRole
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\Tool",
     *     cascade={"persist"}, inversedBy="workspaceToolRoles"
     * )
     * @ORM\JoinColumn(name="tool_id", referencedColumnName="id")
     */
    protected $tool;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace",
     *     cascade={"persist"}, inversedBy="workspaceToolRoles"
     * )
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    protected $workspace;


    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     */
    protected $role;

    /**
     * @ORM\Column(name="display_order", type="integer")
     */
    protected $order;

    public function getId()
    {
        return $this->id;
    }

    public function getWorkspaceTool()
    {
        return $this->workspaceTool;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setTool($tool)
    {
        $this->tool = $tool;
    }

    public function getTool()
    {
        return $this->tool;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function setWorkspace($ws)
    {
        $this->workspace = $ws;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return $this->order;
    }
}
