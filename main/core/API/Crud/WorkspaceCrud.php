<?php

namespace Claroline\CoreBundle\API\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
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
     * @param DeleteEvent $event
     */
    public function preDelete(DeleteEvent $event)
    {
        $workspace = $event->getObject();
        $this->logListener->disable();
        // Log action
        $this->om->startFlushSuite();
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

    /**
     * @param CreateEvent $event
     *
     * @return Workspace
     */
    public function preCreate(CreateEvent $event)
    {
        $workspace = $this->manager->createWorkspace($event->getObject());
        $options = $event->getOptions();

        $user = $this->tokenStorage->getToken() ?
            $this->tokenStorage->getToken()->getUser() :
            $this->userManager->getDefaultClarolineAdmin();
        $model = $workspace->getWorkspaceModel() ? $workspace->getWorkspaceModel() : $this->manager->getDefaultModel();
        $workspace->setWorkspaceModel($model);

        if ($user instanceof User) {
            $workspace->setCreator($user);

            $organization = $user->getMainOrganization() ?
                $user->getMainOrganization() :
                $this->organizationManager->getDefault();
            $workspace->addOrganization($organization);
        }

        //this is for workspace creation: TODO remove that because it's very confusing
        //make a search on LIGHT_COPY you'll find what will probably need a change
        if (in_array(Options::LIGHT_COPY, $options)) {
            $this->om->flush();

            return $workspace;
        }

        //is this part ever fired anymore ? I don't know

        $workspace = $this->manager->copy($model, $workspace, false);

        $this->om->flush();

        return $workspace;
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
}
