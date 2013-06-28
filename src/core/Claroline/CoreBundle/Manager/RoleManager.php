<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Writer\RoleWriter;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.role_manager")
 */
class RoleManager
{
    private $roleWriter;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "roleWriter" = @DI\Inject("claroline.writer.role_writer")
     * })
     */
    public function __contruct(RoleWriter $roleWriter)
    {
        $this->roleWriter = $roleWriter;
    }

    public function createWorkspaceRole($name, $translationKey, AbstractWorkspace $workspace, $isReadOnly = false)
    {
        return $this->roleWriter->createRole($name, $translationKey, $isReadOnly, Role::WS_ROLE, $workspace);
    }

    public function createBaseRole($name, $translationKey, $isReadOnly = true)
    {
        return $this->roleWriter->createRole($name, $translationKey, $isReadOnly, Role::BASE_ROLE, null);
    }

    public function createCustomRole($name, $translationKey, $isReadOnly = false)
    {
        return $this->roleWriter->createRole($name, $translationKey, $isReadOnly, Role::CUSTOM_ROLE, null);
    }

    public function associateRole(AbstractRoleSubject $ars, Role $role)
    {
        $this->roleWriter->associateRole($ars, $role);
    }

    public function dissociateRole(AbstractRoleSubject $ars, Role $role)
    {
        $this->roleWriter->dissociateRole($ars, $role);
    }

    public function associateRoles(AbstractRoleSubject $ars, ArrayCollection $roles)
    {
        foreach ($roles as $role) {
            $this->roleWriter->associateRole($ars, $role);
        }
    }
}