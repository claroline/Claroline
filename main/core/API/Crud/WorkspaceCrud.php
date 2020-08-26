<?php

namespace Claroline\CoreBundle\API\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Listener\Log\LogListener;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WorkspaceCrud
{
    /**
     * WorkspaceCrud constructor.
     *
     * @param WorkspaceManager      $manager
     * @param UserManager           $userManager
     * @param TokenStorageInterface $tokenStorage
     * @param ResourceManager       $resourceManager
     * @param RoleManager           $roleManager
     * @param OrganizationManager   $orgaManager
     * @param ObjectManager         $om
     * @param Crud                  $crud
     * @param StrictDispatcher      $dispatcher
     * @param LogListener           $logListener
     */
    public function __construct(
        WorkspaceManager $manager,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        ResourceManager $resourceManager,
        RoleManager $roleManager,
        OrganizationManager $orgaManager,
        ObjectManager $om,
        Crud $crud,
        StrictDispatcher $dispatcher,
        LogListener $logListener
    ) {
        $this->manager = $manager;
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
        $this->resourceManager = $resourceManager;
        $this->organizationManager = $orgaManager;
        $this->roleManager = $roleManager;
        $this->om = $om;
        $this->crud = $crud;
        $this->dispatcher = $dispatcher;
        $this->logListener = $logListener;
    }

    /**
     * @param CreateEvent $event
     */
    public function preCreate(CreateEvent $event)
    {
        $this->logListener->disable();

        $workspace = $event->getObject();

        $user = $this->tokenStorage->getToken() ?
            $this->tokenStorage->getToken()->getUser() :
            $this->userManager->getDefaultClarolineAdmin();
        $model = $workspace->getWorkspaceModel() ? $workspace->getWorkspaceModel() : $this->manager->getDefaultModel();
        $workspace->setWorkspaceModel($model);

        if ($user instanceof User && empty($workspace->getCreator())) {
            if (empty($workspace->getCreator())) {
                $workspace->setCreator($user);

                if (empty($workspace->getOrganizations()) && !empty($user->getMainOrganization())) {
                    $workspace->addOrganization($user->getMainOrganization());
                }
            }
        }

        // adds default organization if needed
        if (empty($workspace->getOrganizations())) {
            $workspace->addOrganization($this->organizationManager->getDefault());
        }

        $this->logListener->enable();
    }

    /**
     * @param CopyEvent $event
     */
    public function preCopy(CopyEvent $event)
    {
        $this->logListener->disable();
        $workspace = $event->getObject();

        $new = $event->getCopy();
        $new->refreshUuid();

        $this->manager->copy($workspace, $new);
        $this->logListener->enable();
    }

    /**
     * @param UpdateEvent $event
     */
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

    /**
     * @param DeleteEvent $event
     */
    public function preDelete(DeleteEvent $event)
    {
        $workspace = $event->getObject();
        $this->logListener->disable();
        // Log action
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

        $tabs = $this->om->getRepository(HomeTab::class)->findBy(['workspace' => $workspace]);

        foreach ($tabs as $tab) {
            $this->crud->delete($tab);
        }

        $this->dispatcher->dispatch(
            'claroline_workspaces_delete',
            'GenericData',
            [[$workspace]]
        );
        $this->om->remove($workspace);
        $this->om->endFlushSuite();
        $this->logListener->enable();
    }
}
