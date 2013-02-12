<?php

namespace Claroline\CoreBundle\Entity\Tool;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WorkpaceToolRoleRepository");
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
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     */
    protected $role;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\WorkspaceOrderedTool",
     *     cascade={"persist"}, inversedBy="workspaceToolRoles"
     * )
     * @ORM\JoinColumn(name="ordered_tool_id", referencedColumnName="id")
     */
    protected $workspaceOrderedTool;

    public function getId()
    {
        return $this->id;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function setWorkspaceOrderedTool(WorkspaceOrderedTool $ot)
    {
        $this->workspaceOrderedTool = $ot;
    }

    public function getWorkspaceOrderedTool()
    {
        return $this->workspaceOrderedTool;
    }
}
