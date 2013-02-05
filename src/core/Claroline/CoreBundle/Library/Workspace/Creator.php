<?php

namespace Claroline\CoreBundle\Library\Workspace;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceContext;
use Claroline\CoreBundle\Entity\Tool\WorkspaceToolRole;
use Claroline\CoreBundle\Entity\Tool\WorkspaceTool;

class Creator
{
    private $entityManager;
    private $rightManager;
    private $roleRepo;

    public function __construct(EntityManager $em, RightManager $rm)
    {
        $this->entityManager = $em;
        $this->rightManager = $rm;
        $this->roleRepo = $this->entityManager->getRepository('ClarolineCoreBundle:Role');
    }

    /**
     * Creates a workspace.
     *
     * @param \Claroline\CoreBundle\Library\Workspace\Configuration $config
     * @param \Claroline\CoreBundle\Entity\User $manager
     *
     * @return AbstractWorkspace
     */
    public function createWorkspace(Configuration $config, User $manager)
    {
        $config->check();

        $workspaceType = $config->getWorkspaceType();
        $workspace = new $workspaceType;
        $workspace->setName($config->getWorkspaceName());
        $workspace->setPublic($config->isPublic());
        $workspace->setCode($config->getWorkspaceCode());
        $this->entityManager->persist($workspace);
        $this->entityManager->flush();
        $this->initBaseRoles($workspace, $config);

        $rootDir = new Directory();
        $rootDir->setName("{$workspace->getName()} - {$workspace->getCode()}");
        $rootDir->setCreator($manager);
        $directoryType = $this->entityManager
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(array('name' => 'directory'));
        $directoryIcon = $this->entityManager
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon')
            ->findOneBy(array('type' => 'directory', 'iconType' => 1));
        $rootDir->setIcon($directoryIcon);
        $rootDir->setResourceType($directoryType);
        $rootDir->setWorkspace($workspace);
        $this->setResourceOwnerRights(true, true, true, true, true, $rootDir);
        $this->entityManager->persist($rootDir);

        //default resource rights
        foreach($config->getRootPermissions() as $role => $permission) {
            $this->createDefaultsResourcesRights(
                $permission['canDelete'],
                $permission['canOpen'],
                $permission['canEdit'],
                $permission['canCopy'],
                $permission['canExport'],
                $permission['canCopy'],
                $this->roleRepo->findOneBy(array('name' => $role.'_'.$workspace->getId())),
                $rootDir,
                $workspace
            );
        }

        $this->createDefaultsResourcesRights(
            false, false, false, false, false, false,
            $this->roleRepo->findOneBy(array('name' => 'ROLE_ANONYMOUS')),
            $rootDir,
            $workspace
        );

        $this->createDefaultsResourcesRights(
            true, true, true, true, true, true,
            $this->roleRepo->findOneBy(array('name' => 'ROLE_ADMIN')),
            $rootDir,
            $workspace
        );

        $manager->addRole($this->roleRepo->getManagerRole($workspace));
        $this->addMandatoryTools($workspace, $config);

        $this->entityManager->persist($manager);
        $this->entityManager->flush();

        return $workspace;
    }

    /**
     * Create default permissions for a role and a resource.
     *
     * @param boolean $canDelete
     * @param boolean $canOpen
     * @param boolean $canEdit
     * @param boolean $canCopy
     * @param boolean $canExport
     * @param boolean $canCreate
     * @param \Claroline\CoreBundle\Entity\Role $role
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceContext
     */
    private function createDefaultsResourcesRights(
        $canDelete,
        $canOpen,
        $canEdit,
        $canCopy,
        $canExport,
        $canCreate,
        Role $role,
        AbstractResource $resource,
        AbstractWorkspace $workspace
    )
    {
        $resourceContext = new ResourceContext();
        $resourceContext->setCanCopy($canCopy);
        $resourceContext->setCanDelete($canDelete);
        $resourceContext->setCanEdit($canEdit);
        $resourceContext->setCanOpen($canOpen);
        $resourceContext->setCanExport($canExport);
        $resourceContext->setRole($role);
        $resourceContext->setResource($resource);
        $resourceContext->setWorkspace($workspace);

        if ($canCreate) {
            $resourceTypes = $this->entityManager
                ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
                ->findBy(array('isVisible' => true));

            foreach ($resourceTypes as $resourceType) {
                $resourceContext->addResourceType($resourceType);
            }
        }

        $this->entityManager->persist($resourceContext);

        return $resourceContext;
    }

    /**
     * Creates the base roles of a workspace.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Library\Workspace\Configuration $config
     */
    private function initBaseRoles(AbstractWorkspace $workspace, Configuration $config)
    {
        $roles = $config->getRoles();

        foreach ($roles as $name => $translation){
            $this->createRole($name, $workspace, $translation);
        }

        $this->entityManager->flush();
    }

    /**
     * Creates a new role.
     *
     * @param string $baseName
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param string $translationKey
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    private function createRole($baseName, AbstractWorkspace $workspace, $translationKey)
    {
        $baseRole = new Role();
        $baseRole->setName($baseName . '_' . $workspace->getId());
        $baseRole->setParent(null);
        $baseRole->setRoleType(Role::WS_ROLE);
        $baseRole->setTranslationKey($translationKey);
        $baseRole->setWorkspace($workspace);

        $this->entityManager->persist($baseRole);

        return $baseRole;
    }

    private function setResourceOwnerRights(
        $isSharable,
        $isEditable,
        $isDeletable,
        $isExportable,
        $isCopiable,
        AbstractResource $resource
    )
    {
        $resource->setSharable($isSharable);
        $resource->setEditable($isEditable);
        $resource->setDeletable($isDeletable);
        $resource->setExportable($isExportable);
        $resource->setCopiable($isCopiable);
        $resourceTypes = $this->entityManager
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findBy(array('isVisible' => true));

        foreach ($resourceTypes as $resourceType) {
            $resource->addResourceTypeCreation($resourceType);
        }

        return $resource;
    }

    /**
     * Adds the tools for a workspace.
     *
     * @todo Optimize this for doctrine (loops with findby aren't exactly really effective).
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Library\Workspace\Configuration $config
     */
    private function addMandatoryTools(AbstractWorkspace $workspace, Configuration $config)
    {
        $tools = $config->getTools();

        foreach ($tools as $name => $roles) {
            $tool = $this->entityManager
                ->getRepository('ClarolineCoreBundle:Tool\Tool')
                ->findOneBy(array('name' => $name));
            $wsTool = new WorkspaceTool();
            $wsTool->setTool($tool);
            $wsTool->setWorkspace($workspace);

            $this->entityManager->persist($wsTool);

            foreach ($roles as $role) {
                $role = $this->entityManager
                    ->getRepository('ClarolineCoreBundle:Role')
                    ->findOneBy(array('name' => $role.'_'.$workspace->getId()));
                $this->setWorkspaceToolRole($wsTool, $role);
            }
        }

        $this->entityManager->persist($workspace);
    }

    private function setWorkspaceToolRole(WorkspaceTool $wsTool, Role $role)
    {
        $wtr = new WorkspaceToolRole();
        $wtr->setRole($role);
        $wtr->setWorkspaceTool($wsTool);

        $this->entityManager->persist($wtr);
    }
}