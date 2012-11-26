<?php

namespace Claroline\CoreBundle\Entity;

use \RuntimeException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Security\SymfonySecurity;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_role")
 */
class WorkspaceRole extends Role
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace", inversedBy="roles")
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    protected $workspace;

    /**
     * @ORM\ManyToMany(
     *  targetEntity="Claroline\CoreBundle\Entity\Group",
     *  mappedBy="workspaceRoles"
     * )
     * @ORM\JoinTable(name="claro_group_role",
     *     joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->groups = new ArrayCollection();
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
            throw new RuntimeException('Workspace base roles cannot be modified');
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
            throw new RuntimeException(
                "This role is already bound to workspace '{$ws->getName()}'"
            );
        }

        $this->workspace = $workspace;
    }

    public function getGroups()
    {
        return $this->groups;
    }
}