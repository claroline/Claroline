<?php

namespace Claroline\CoreBundle\API\Crud\Cryptography;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ApiToken
{
    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param CreateEvent $event
     */
    public function preCreate(CreateEvent $event)
    {
        /** @var Role $role */
        $apiToken = $event->getObject();
        $apiToken->setUser($this->tokenStorage->getToken()->getUser());
    }
}
