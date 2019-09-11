<?php

namespace Claroline\CoreBundle\API\Crud;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
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
     * @param WorkspaceManager $manager
     */
    public function __construct(
        WorkspaceManager $manager,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        ResourceManager $resourceManager,
        RoleManager $roleManager,
        OrganizationManager $orgaManager,
        ObjectManager $om
    ) {
        $this->manager = $manager;
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
        $this->resourceManager = $resourceManager;
        $this->organizationManager = $orgaManager;
        $this->roleManager = $roleManager;
        $this->om = $om;
    }

    /**
     * @param DeleteEvent $event
     */
    public function preDelete(DeleteEvent $event)
    {
        $this->manager->deleteWorkspace($event->getObject());
    }

    /**
     * @param CreateEvent $event
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
        $workspace = $event->getObject();
        $options = $event->getOptions();

        $new = $event->getCopy();
        $new->refreshUuid();

        $this->manager->copy($workspace, $new, in_array(Options::WORKSPACE_MODEL, $options));
    }

    /**
     * @param CopyEvent $event
     */
    public function postUpdate(UpdateEvent $event)
    {
        $workspace = $event->getObject();
        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        $root->setName($workspace->getName());
        $this->om->persist($root);
        $this->om->flush();
    }
}
