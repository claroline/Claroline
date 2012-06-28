<?php

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Exception\ClarolineException;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Security\SymfonySecurity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_role")
 */
class WorkspaceRole extends Role
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace")
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


    /**
     * @ORM\Column(name="res_mask", type="integer")
     */
    private $resMask;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->res_mask = 0;
    }

    /**
     * Sets the role's name. This operation is only needed for workspace custom roles.
     * Note that in this case, the 'ROLE_' convention isn't mandatory.
     *
     * @param string $name
     */
    public function setName($name)
    {
        if (AbstractWorkspace::isBaseRole($this->getName())) {
            throw new ClarolineException('Workspace base roles cannot be modified');
        }

        if (AbstractWorkspace::isBaseRole($name)) {
            $this->setReadOnly(true);
        }

        $this->name = $name;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Binds the role to a workspace instance. This method is aimed to be used
     * by the AbstractWorkspace role setters.
     *
     * @param AbstractWorkspace $workspace
     */
    public function setWorkspace(AbstractWorkspace $workspace)
    {
        $ws = $this->getWorkspace();

        if (null !== $ws) {
            throw new ClarolineException(
                "This role is already bound to workspace '{$ws->getName()}'"
            );
        }

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

    /**
     * Gets the resource mask
     *
     * @return integer
     */
    public function getResMask()
    {
        return $this->resMask;
    }

    /**
     * Sets the resource mask
     *
     * @param integer $resMask
     */
    public function setResMask($resMask)
    {
        $this->resMask = $resMask;
    }

    /**
     * Adds a permission
     *
     * @param integer $mask
     */
    public function addResourcePermission($mask)
    {
        $builder = new MaskBuilder($this->getResMask());
        $builder->add($mask);
        $resMask = $builder->get();
        $this->setResMask($resMask);
    }

    /**
     * Removes a permission
     *
     * @param integer $mask
     */
    public function removeResourcePermission($mask)
    {
        $builder = new MaskBuilder($this->getResMask());
        $builder->remove($mask);
        $resMask = $builder->get();
        $this->setResMask($resMask);
    }

    /**
     * Returns the permission array
     */
    public function getPermissions()
    {
        return SymfonySecurity::getArrayPermissions($this->getResMask());
    }
}