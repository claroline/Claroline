<?php

namespace Claroline\CommunityBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AddRoleEvent;
use Claroline\CoreBundle\Event\Security\RemoveRoleEvent;
use Claroline\CoreBundle\Manager\FileManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GroupSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var FileManager */
    private $fileManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        StrictDispatcher $dispatcher,
        FileManager $fileManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $dispatcher;
        $this->fileManager = $fileManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', Group::class) => 'preCreate',
            Crud::getEventName('create', 'post', Group::class) => 'postCreate',
            Crud::getEventName('patch', 'post', Group::class) => 'postPatch',
            Crud::getEventName('delete', 'post', Group::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var Group $group */
        $group = $event->getObject();
        $user = $this->tokenStorage->getToken()->getUser();

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

            $users = array_filter($group->getUsers()->toArray(), function (User $user) use ($role) {
                return $user->isEnabled() && !$user->isRemoved() && !$user->hasRole($role->getName(), false);
            });

            if ('add' === $event->getAction()) {
                $this->dispatcher->dispatch(SecurityEvents::ADD_ROLE, AddRoleEvent::class, [$users, $role]);
            } elseif ('remove' === $event->getAction()) {
                $this->dispatcher->dispatch(SecurityEvents::REMOVE_ROLE, RemoveRoleEvent::class, [$users, $role]);
            }
        } elseif ($event->getValue() instanceof User) {
            $user = $event->getValue();

            foreach ($group->getEntityRoles() as $role) {
                if (!$user->hasRole($role->getName(), false)) {
                    if ('add' === $event->getAction()) {
                        $this->dispatcher->dispatch(SecurityEvents::ADD_ROLE, AddRoleEvent::class, [[$user], $role]);
                    } elseif ('remove' === $event->getAction()) {
                        $this->dispatcher->dispatch(SecurityEvents::REMOVE_ROLE, RemoveRoleEvent::class, [[$user], $role]);
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
