<?php

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Workspace;
use Claroline\CoreBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_role")
 */
class WorkspaceRole extends Role
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace")
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    private $workspace;
    
    /**
     * @ORM\ManyToMany(
     *  targetEntity="Claroline\CoreBundle\Entity\User", 
     *  inversedBy="workspaceRoles"
     * )
     * @ORM\JoinTable(name="claro_user_role",
     *     joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     */
    private $users;
    
    /**
     * @ORM\ManyToMany(
     *  targetEntity="Claroline\CoreBundle\Entity\Group", 
     *  inversedBy="workspaceRoles"
     * )
     * @ORM\JoinTable(name="claro_group_role",
     *     joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    private $groups;
    
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }
    
    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function getGroups()
    {
        return $this->groups;
    }
}