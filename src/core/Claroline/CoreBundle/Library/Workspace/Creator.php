<?php

namespace Claroline\CoreBundle\Library\Workspace;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Rights\ResourceRights;
use Claroline\CoreBundle\Entity\Rights\WorkspaceRights;

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

    public function createWorkspace(Configuration $config, User $manager)
    {
        $config->check();

        $workspaceType = $config->getWorkspaceType();
        $workspace = new $workspaceType;
        $workspace->setName($config->getWorkspaceName());
        $workspace->setPublic($config->isPublic());
        $workspace->setType($config->getType());
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
        $this->entityManager->persist($rootDir);
//        $this->entityManager->flush();

        //default resource rights
        $this->createDefaultsResourcesRights(true, true, true, true, true, true, true, $this->roleRepo->getManagerRole($workspace), $rootDir);
        $this->createDefaultsResourcesRights(true, false, true, false, false, true, false, $this->roleRepo->getCollaboratorRole($workspace), $rootDir);
        $this->createDefaultsResourcesRights(false, false, false, false, false, false, false, $this->roleRepo->getVisitorRole($workspace), $rootDir);
        $this->createDefaultsResourcesRights(false, false, false, false, false, false, false, $this->roleRepo->findOneBy(array ('name' => 'ROLE_ANONYMOUS')), $rootDir);

        //default workspace rights
        $this->createDefaultsWorkspaceRights(true, true, true, true, $this->roleRepo->getManagerRole($workspace), $workspace);
        $this->createDefaultsWorkspaceRights(true, false, false, false, $this->roleRepo->getCollaboratorRole($workspace), $workspace);
        $this->createDefaultsWorkspaceRights(true, false, false, false, $this->roleRepo->getVisitorRole($workspace), $workspace);
        $this->createDefaultsWorkspaceRights(false, false, false, false, $this->roleRepo->findOneBy(array ('name' => 'ROLE_ANONYMOUS')), $workspace);

        $manager->addRole($this->roleRepo->getManagerRole($workspace));
        $this->entityManager->persist( $manager);
        $this->entityManager->flush();
//        $this->entityManager->detach($rootDir);
        //for some reason, it broke the test suite... and that's all.
//      $this->entityManager->detach($workspace);

        return $workspace;
    }

    /**
     * Creates a ResourceRights entity (will be used as the default one)
     *
     * @param boolean $canView
     * @param boolean $canDelete
     * @param boolean $canOpen
     * @param boolean $canEdit
     * @param boolean $canCopy
     * @param boolean $canExport
     * @param boolean $canCreate
     *
     * @return ResourceRights
     */
    private function createDefaultsResourcesRights($canView, $canDelete, $canOpen, $canEdit, $canCopy, $canExport, $canCreate, $role, $resource)
    {
        $resourceRight = new ResourceRights();

        $resourceRight->setCanCopy($canCopy);
        $resourceRight->setCanDelete($canDelete);
        $resourceRight->setCanEdit($canEdit);
        $resourceRight->setCanOpen($canOpen);
        $resourceRight->setCanView($canView);
        $resourceRight->setCanExport($canExport);
        $resourceRight->setRole($role);
        $resourceRight->setResource($resource);

        if($canCreate){
            $resourceTypes = $this->entityManager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findBy(array('isVisible' => true));

            foreach($resourceTypes as $resourceType){
                $resourceRight->addResourceType($resourceType);
            }
        }

        $this->entityManager->persist($resourceRight);

        return $resourceRight;
    }

    private function createDefaultsWorkspaceRights($canView, $canManage, $canEdit, $canDelete, $role, $workspace)
    {
        $workspaceRight = new WorkspaceRights();
        $workspaceRight->setCanView($canView);
        $workspaceRight->setCanManage($canManage);
        $workspaceRight->setCanEdit($canEdit);
        $workspaceRight->setCanDelete($canDelete);
        $workspaceRight->setRole($role);
        $workspaceRight->setWorkspace($workspace);

        $this->entityManager->persist($workspaceRight);
    }

     private function initBaseRoles($workspace, Configuration $config)
     {
         $this->createRole('VISITOR', $workspace, $config->getVisitorTranslationKey());
         $this->createRole('COLLABORATOR', $workspace, $config->getCollaboratorTranslationKey());
         $this->createRole('MANAGER', $workspace, $config->getManagerTranslationKey());

         $this->entityManager->flush();
     }

     private function createRole($baseName, $workspace, $translationKey)
     {
        $baseRole = new Role();
        $baseRole->setName('ROLE_WS_'.$baseName.'_'.$workspace->getId());
        $baseRole->setParent(null);
        $baseRole->setRoleType(Role::WS_ROLE);
        $baseRole->setTranslationKey($translationKey);

        $this->entityManager->persist($baseRole);

        return $baseRole;
     }
}