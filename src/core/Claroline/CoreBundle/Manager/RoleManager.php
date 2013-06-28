<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Writer\RoleWriter;
use Claroline\CoreBundle\Database\Writer;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.role_manager")
 */
class RoleManager
{
    private $roleWriter;
    private $writer;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "roleWriter" = @DI\Inject("claroline.writer.role_writer"),
     *     "writer" = @DI\Inject("claroline.database.writer")
     * })
     */
    public function __contruct(RoleWriter $roleWriter, Writer $writer)
    {
        $this->roleWriter = $roleWriter;
        $this->writer = $writer;
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

    public function initWorkspaceBaseRole(array $roles, AbstractWorkspace $workspace)
    {
        $entityRoles = array();

        foreach ($roles as $name => $translation) {
            $role = new Role();
            $role->setName("{$name}_{$workspace->getId()}");
            $role->setTranslationKey($translation);
            $role->setReadOnly(false);
            $role->setType(Role::WS_ROLE);
            $role->setWorkspace($workspace);
            $this->writer->create($role);
            $entityRoles[$name] = $role;
        }

        return $entityRoles;
    }

//    public function bind(Role $role, AbstractRoleSubject $users)
//    {
//        $this->roleWriter->bind($role, $users);
//    }
}