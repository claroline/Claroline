<?php

namespace Claroline\CommunityBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AddRoleEvent;
use Claroline\CoreBundle\Event\Security\RemoveRoleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GroupSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var StrictDispatcher */
    private $dispatcher;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        StrictDispatcher $dispatcher
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $dispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', Group::class) => 'preCreate',
            Crud::getEventName('patch', 'post', Group::class) => 'postPatch',
        ];
    }

    public function preCreate(CreateEvent $event)
    {
        /** @var Group $group */
        $group = $event->getObject();
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof User) {
            $group->addOrganization($user->getMainOrganization());
        }
    }

    public function postPatch(PatchEvent $event)
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
}
