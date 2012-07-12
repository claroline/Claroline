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
        $this->entityManager->persist($workspace);
        $this->entityManager->flush();
        $workspace->initBaseRoles();
        $workspace->getVisitorRole()->setTranslationKey($config->getVisitorTranslationKey());
        $workspace->getVisitorRole()->setResMask(MaskBuilder::MASK_VIEW);
        $workspace->getCollaboratorRole()->setTranslationKey($config->getCollaboratorTranslationKey());
        $workspace->getCollaboratorRole()->setResMask(MaskBuilder::MASK_VIEW);
        $workspace->getManagerRole()->setTranslationKey($config->getManagerTranslationKey());
        $workspace->getManagerRole()->setResMask(MaskBuilder::MASK_OWNER);
        $root = new ResourceInstance();
        $rootDir = new Directory();
        $rootDir->setName($workspace->getName());
        $rootDir->setShareType(0);
        $rootDir->setCreator($manager);
        $directoryType = $this->entityManager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneBy(array('type' => 'directory'));
        $rootDir->setResourceType($directoryType);
        $root->setResource($rootDir);
        $root->setCopy(0);
        $root->setWorkspace($workspace);
        $root->setCreator($manager);
        
        $this->entityManager->persist($rootDir);
        $this->entityManager->persist($root);
        $this->entityManager->flush();

        if (null !== $manager) {
            $manager->addRole($workspace->getManagerRole());
            $this->rightManager->addRight($workspace, $manager, MaskBuilder::MASK_OWNER);
        }

        $this->entityManager->flush();

        $roles = $workspace->getWorkspaceRoles();
        $masks = \Claroline\CoreBundle\Library\Security\SymfonySecurity::getSfMasks();
        $keys = array_keys($masks);

        foreach ($roles as $role) {
            $mask = $role->getResMask();
            foreach ($keys as $key) {
                if ($mask & $key) {
                    $this->rightManager->addRight($root, $role, $key);
                }
            }
        }

        return $workspace;
    }
}