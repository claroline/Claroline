<?php

namespace Claroline\CoreBundle\API\Crud\User;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AuthenticationBundle\Security\Authentication\Authenticator;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GroupCrud
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var Authenticator */
    private $authenticator;
    /** @var RoleManager */
    private $roleManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        Authenticator $authenticator,
        RoleManager $roleManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticator = $authenticator;
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
        /** @var User $currentUser */
        $currentUser = $this->tokenStorage->getToken()->getUser();

        // refresh token to get updated roles if the current user is in the group
        if ('role' === $event->getProperty() && $currentUser instanceof User && $group->containsUser($currentUser)) {
            $this->authenticator->createToken($currentUser);
        }
    }
}
