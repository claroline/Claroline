<?php

namespace Claroline\CoreBundle\API\Crud;

use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Listener\Log\LogListener;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WorkspaceCrud
{
    private $manager;
    private $tokenStorage;
    private $resourceManager;
    private $organizationManager;
    private $om;
    private $logListener;

    public function __construct(
        WorkspaceManager $manager,
        TokenStorageInterface $tokenStorage,
        ResourceManager $resourceManager,
        OrganizationManager $orgaManager,
        ObjectManager $om,
        LogListener $logListener
    ) {
        $this->manager = $manager;
        $this->tokenStorage = $tokenStorage;
        $this->resourceManager = $resourceManager;
        $this->organizationManager = $orgaManager;
        $this->om = $om;
        $this->logListener = $logListener;
    }

    public function preCreate(CreateEvent $event)
    {
        $this->logListener->disable();

        $workspace = $event->getObject();

        $model = $workspace->getWorkspaceModel() ? $workspace->getWorkspaceModel() : $this->manager->getDefaultModel();
        $workspace->setWorkspaceModel($model);

        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        if ($user instanceof User && empty($workspace->getCreator())) {
            $workspace->setCreator($user);

            if (empty($workspace->getOrganizations()) && !empty($user->getMainOrganization())) {
                $workspace->addOrganization($user->getMainOrganization());
            }
        }

        // adds default organization if needed
        if (empty($workspace->getOrganizations())) {
            $workspace->addOrganization($this->organizationManager->getDefault());
        }

        $this->logListener->enable();
    }

    public function preCopy(CopyEvent $event)
    {
        $this->logListener->disable();
        $workspace = $event->getObject();

        $new = $event->getCopy();
        $new->refreshUuid();

        $this->manager->copy($workspace, $new);
        $this->logListener->enable();
    }

    public function endUpdate(UpdateEvent $event)
    {
        $workspace = $event->getObject();
        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        if ($root) {
            $root->setName($workspace->getName());
            $this->om->persist($root);
            $this->om->flush();
        }
    }

    public function preDelete(DeleteEvent $event)
    {
        $workspace = $event->getObject();
        $this->logListener->disable();

        $this->om->startFlushSuite();
        /** @var ResourceNode[] $roots */
        $roots = $this->om->getRepository(ResourceNode::class)->findBy(['workspace' => $workspace, 'parent' => null]);

        //in case 0 or multiple due to errors
        foreach ($roots as $root) {
            $children = $root->getChildren();

            if ($children) {
                foreach ($children as $node) {
                    $this->resourceManager->delete($node);
                }
            }
        }

        $this->om->remove($workspace);
        $this->om->endFlushSuite();

        $this->logListener->enable();
    }
}
