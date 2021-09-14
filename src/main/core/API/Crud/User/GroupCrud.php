<?php

namespace Claroline\CoreBundle\API\Crud\User;

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
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GroupCrud
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var RoleManager */
    private $roleManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        StrictDispatcher $dispatcher,
        RoleManager $roleManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $dispatcher;
        $this->roleManager = $roleManager;
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

    public function prePatch(PatchEvent $event)
    {
        /** @var Group $group */
        $group = $event->getObject();

        // trying to add a new role to a group
        if (Crud::COLLECTION_ADD === $event->getAction() && 'role' === $event->getProperty()) {
            /** @var Role $role */
            $role = $event->getValue();

            if ($group->hasRole($role->getName()) || !$this->roleManager->validateRoleInsert($event->getObject(), $event->getValue())) {
                $event->block();
            }
        }
    }

    public function postPatch(PatchEvent $event)
    {
        /** @var Group $group */
        $group = $event->getObject();

        if ($event->getValue() instanceof Role) {
            foreach ($group->getUsers() as $user) {
                if ($user->isEnabled() && !$user->isRemoved()) {
                    if ('add' === $event->getAction()) {
                        $this->dispatcher->dispatch(SecurityEvents::ADD_ROLE, AddRoleEvent::class, [[$user], $event->getValue()]);
                    } elseif ('remove' === $event->getAction()) {
                        $this->dispatcher->dispatch(SecurityEvents::REMOVE_ROLE, RemoveRoleEvent::class, [[$user], $event->getValue()]);
                    }
                }
            }
        }
    }
}
