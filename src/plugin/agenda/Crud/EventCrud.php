<?php

namespace Claroline\AgendaBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EventCrud
{
    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage, UserManager $userManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
    }

    /**
     * @param CreateEvent $event
     */
    public function preCreate(CreateEvent $event)
    {
        /** @var Group $role */
        $object = $event->getObject();
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof User) {
            $object->setUser($user);
        } else {
            $object->setUser($this->userManager->getDefaultClarolineUser());
        }
    }
}
