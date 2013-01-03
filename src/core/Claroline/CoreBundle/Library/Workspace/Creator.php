<?php

namespace Claroline\CoreBundle\Library\Workspace;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Workspace\ResourceRights;

class Creator
{
    private $entityManager;
    private $rightManager;

    public function __construct(EntityManager $em, RightManager $rm)
    {
        $this->entityManager = $em;
        $this->rightManager = $rm;
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
        $workspace->initBaseRoles();
        $workspace->getVisitorRole()->setTranslationKey($config->getVisitorTranslationKey());
        $workspace->getCollaboratorRole()->setTranslationKey($config->getCollaboratorTranslationKey());
        $workspace->getManagerRole()->setTranslationKey($config->getManagerTranslationKey());
        $this->entityManager->persist($workspace);
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

        $this->createDefaultsResourcesRights(true, true, true, true, true, true, true, $workspace->getManagerRole(), $rootDir);
        $this->createDefaultsResourcesRights(true, false, true, false, false, false, true, $workspace->getCollaboratorRole(), $rootDir);
        $this->createDefaultsResourcesRights(false, false, false, false, false, false, false, $workspace->getVisitorRole(), $rootDir);

        if (null !== $manager) {
            $manager->addRole($workspace->getManagerRole());
            $this->rightManager->addRight($workspace, $manager, MaskBuilder::MASK_OWNER);
        }

//      $this->entityManager->flush();
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
     * @param boolean $canShare
     * @param boolean $canCreate
     *
     * @return ResourceRights
     */
    private function createDefaultsResourcesRights($canView, $canDelete, $canOpen, $canEdit, $canCopy, $canCreate, $canExport, $role, $resource)
    {

        $resourceRight = new ResourceRights();

        $resourceRight->setCanCopy($canCopy);
        $resourceRight->setCanDelete($canDelete);
        $resourceRight->setCanEdit($canEdit);
        $resourceRight->setCanOpen($canOpen);
        $resourceRight->setCanView($canView);
        $resourceRight->setCanCreate($canCreate);
        $resourceRight->setCanExport($canExport);
        $resourceRight->setRole($role);
        $resourceRight->setResource($resource);

        $this->entityManager->persist($resourceRight);

        return $resourceRight;
    }
}