<?php

namespace Claroline\CommunityBundle\Subscriber\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AddRoleEvent;
use Claroline\CoreBundle\Event\Security\RemoveRoleEvent;
use Claroline\CoreBundle\Manager\FileManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class GroupSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ObjectManager $om,
        private readonly FileManager $fileManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, Group::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, Group::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, Group::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::POST_PATCH, Group::class) => 'postPatch',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, Group::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var Group $group */
        $group = $event->getObject();
        $user = $this->tokenStorage->getToken()?->getUser();

        if ($user instanceof User) {
            $group->addOrganization($user->getMainOrganization());
        }
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var Group $group */
        $group = $event->getObject();

        if ($group->getPoster()) {
            $this->fileManager->linkFile(Group::class, $group->getUuid(), $group->getPoster());
        }

        if ($group->getThumbnail()) {
            $this->fileManager->linkFile(Group::class, $group->getUuid(), $group->getThumbnail());
        }
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var Group $group */
        $group = $event->getObject();
        $oldData = $event->getOldData();

        $this->fileManager->updateFile(
            Group::class,
            $group->getUuid(),
            $group->getPoster(),
            !empty($oldData['poster']) ? $oldData['poster'] : null
        );

        $this->fileManager->updateFile(
            Group::class,
            $group->getUuid(),
            $group->getThumbnail(),
            !empty($oldData['thumbnail']) ? $oldData['thumbnail'] : null
        );
    }

    public function postPatch(PatchEvent $event): void
    {
        /** @var Group $group */
        $group = $event->getObject();

        if ($event->getValue() instanceof Role) {
            $role = $event->getValue();

            $users = $this->om->getRepository(User::class)->findByGroup($group);

            $users = array_filter($users, function (User $user) use ($role) {
                return !$user->hasRole($role->getName(), false);
            });

            if ('add' === $event->getAction()) {
                $this->dispatcher->dispatch(new AddRoleEvent($users, $role), SecurityEvents::ADD_ROLE);
            } elseif ('remove' === $event->getAction()) {
                $this->dispatcher->dispatch(new RemoveRoleEvent($users, $role), SecurityEvents::REMOVE_ROLE);
            }
        } elseif ($event->getValue() instanceof User) {
            $user = $event->getValue();

            foreach ($group->getEntityRoles() as $role) {
                if (!$user->hasRole($role->getName(), false)) {
                    if ('add' === $event->getAction()) {
                        $this->dispatcher->dispatch(new AddRoleEvent([$user], $role), SecurityEvents::ADD_ROLE);
                    } elseif ('remove' === $event->getAction()) {
                        $this->dispatcher->dispatch(new RemoveRoleEvent([$user], $role), SecurityEvents::REMOVE_ROLE);
                    }
                }
            }
        }
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var Group $group */
        $group = $event->getObject();

        if ($group->getPoster()) {
            $this->fileManager->unlinkFile(Group::class, $group->getUuid(), $group->getPoster());
        }

        if ($group->getThumbnail()) {
            $this->fileManager->unlinkFile(Group::class, $group->getUuid(), $group->getThumbnail());
        }
    }
}
