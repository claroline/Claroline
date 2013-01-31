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
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\WorkspaceTool",
     *     cascade={"persist"}, inversedBy="workspaceToolRoles"
     * )
     * @ORM\JoinColumn(name="workspace_tool_id", referencedColumnName="id")
     */
    protected $workspaceTool;


    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     */
    protected $role;

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

    public function setWorkspaceTool($wsTool)
    {
        $this->workspaceTool = $wsTool;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }


}
