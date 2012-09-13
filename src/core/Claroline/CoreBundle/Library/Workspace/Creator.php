<?php

namespace Claroline\CoreBundle\Library\Workspace;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManager;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;

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
        $root = new ResourceInstance();
        $rootDir = new Directory();
        $root->setName($workspace->getCode().' - '.$workspace->getName());
        $rootDir->setShareType(0);
        $rootDir->setCreator($manager);
        $directoryType = $this->entityManager
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(array('type' => 'directory'));
        $directoryIcon = $this->entityManager
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon')
            ->findOneBy(array('type' => 'directory', 'iconType' => 1));
        $rootDir->setIcon($directoryIcon);
        $rootDir->setResourceType($directoryType);
        $root->setResource($rootDir);
        $root->setWorkspace($workspace);
        $root->setCreator($manager);

        $this->entityManager->persist($rootDir);
        $this->entityManager->persist($root);
        $this->entityManager->flush();
        $workspace->initBaseRoles();
        $workspace->getVisitorRole()->setTranslationKey($config->getVisitorTranslationKey());
        $workspace->getVisitorRole()->setResMask(MaskBuilder::MASK_VIEW);
        $workspace->getCollaboratorRole()->setTranslationKey($config->getCollaboratorTranslationKey());
        $workspace->getCollaboratorRole()->setResMask(MaskBuilder::MASK_VIEW);
        $workspace->getManagerRole()->setTranslationKey($config->getManagerTranslationKey());
        $workspace->getManagerRole()->setResMask(MaskBuilder::MASK_OWNER);
        $this->entityManager->persist($workspace);
        
        if (null !== $manager) {
            $manager->addRole($workspace->getManagerRole());
            $this->rightManager->addRight($workspace, $manager, MaskBuilder::MASK_OWNER);
        }

        $this->entityManager->flush();
        $this->entityManager->detach($rootDir);
        $this->entityManager->detach($root);

        return $workspace;
    }
}