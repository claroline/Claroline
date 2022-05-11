<?php

namespace Claroline\CoreBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSubscriber implements EventSubscriberInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var WorkspaceManager */
    private $workspaceManager;
    /** @var ResourceManager */
    private $resourceManager;

    public function __construct(
        ObjectManager $om,
        WorkspaceManager $workspaceManager,
        ResourceManager $resourceManager
    ) {
        $this->om = $om;
        $this->workspaceManager = $workspaceManager;
        $this->resourceManager = $resourceManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'post', User::class) => 'postCreate',
            Crud::getEventName('update', 'post', User::class) => 'postUpdate',
            Crud::getEventName('delete', 'post', User::class) => 'postDelete',
            'merge_users' => 'mergeUsers',
        ];
    }

    public function postCreate(CreateEvent $event)
    {
        /** @var User $user */
        $user = $event->getObject();
        $options = $event->getOptions();

        if (!in_array(Options::NO_PERSONAL_WORKSPACE, $options)) {
            $createWs = false;
            foreach ($user->getEntityRoles() as $role) {
                if ($role->getPersonalWorkspaceCreationEnabled()) {
                    $createWs = true;
                    break;
                }
            }

            if ($createWs) {
                $this->workspaceManager->createPersonalWorkspace($user);
            }
        }
    }

    public function postUpdate(UpdateEvent $event)
    {
        /** @var User $user */
        $user = $event->getObject();
        $oldData = $event->getOldData();

        if (!empty($oldData) && $oldData['username'] !== $user->getUsername()) {
            // TODO : rename personal WS if user is renamed
        }
    }

    public function postDelete(DeleteEvent $event)
    {
        /** @var User $user */
        $user = $event->getObject();

        // keeping the user's workspace with its original code
        // would prevent creating a user with the same username
        // todo: remove with crud
        $ws = $user->getPersonalWorkspace();

        if ($ws) {
            $ws->setCode($ws->getCode().'#deleted_user#'.$user->getId());
            $ws->setHidden(true);
            $ws->setArchived(true);
            $this->om->persist($ws);
        }
    }

    public function mergeUsers(MergeUsersEvent $event)
    {
        // Replace creator of resource nodes
        $resourcesCount = $this->resourceManager->replaceCreator($event->getRemoved(), $event->getKept());
        $event->addMessage("[CoreBundle] updated resources count: $resourcesCount");

        // Change personal workspace into regular
        if ($event->getRemoved()->getPersonalWorkspace()) {
            $event->getRemoved()->getPersonalWorkspace()->setPersonal(false);
        }
    }
}
