<?php

namespace Claroline\CoreBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly WorkspaceManager $workspaceManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::POST_CREATE, User::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, User::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, User::class) => 'postDelete',
        ];
    }

    public function postCreate(CreateEvent $event): void
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

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var User $user */
        $user = $event->getObject();
        $oldData = $event->getOldData();

        if (!empty($oldData) && $oldData['username'] !== $user->getUsername()) {
            // TODO : rename personal WS if user is renamed
        }
    }

    public function postDelete(DeleteEvent $event): void
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
}
