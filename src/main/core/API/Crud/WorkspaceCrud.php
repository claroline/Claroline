<?php

namespace Claroline\CoreBundle\API\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Listener\Log\LogListener;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WorkspaceCrud
{
    private $manager;
    private $tokenStorage;
    private $crud;
    private $resourceManager;
    private $organizationManager;
    private $om;
    private $logListener;

    public function __construct(
        WorkspaceManager $manager,
        TokenStorageInterface $tokenStorage,
        Crud $crud,
        ResourceManager $resourceManager,
        OrganizationManager $orgaManager,
        ObjectManager $om,
        LogListener $logListener
    ) {
        $this->manager = $manager;
        $this->tokenStorage = $tokenStorage;
        $this->crud = $crud;
        $this->resourceManager = $resourceManager;
        $this->organizationManager = $orgaManager;
        $this->om = $om;
        $this->logListener = $logListener;
    }

    public function preCreate(CreateEvent $event)
    {
        $workspace = $event->getObject();

        $model = $workspace->getWorkspaceModel() ? $workspace->getWorkspaceModel() : $this->manager->getDefaultModel();
        $workspace->setWorkspaceModel($model);

        // set the creator
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

    public function preUpdate(UpdateEvent $event)
    {
        /** @var Workspace $workspace */
        $workspace = $event->getObject();
        $oldData = $event->getOldData();
        if (!empty($oldData['name']) && $oldData['name'] !== $workspace->getName()) {
            $root = $this->resourceManager->getWorkspaceRoot($workspace);
            if ($root) {
                $root->setName($workspace->getName());
                $this->om->persist($root);
            }
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
                    $this->crud->delete($node, [Crud::NO_PERMISSIONS]);
                }
            }
        }

        $this->om->remove($workspace);
        $this->om->endFlushSuite();

        $this->logListener->enable();
    }
}
