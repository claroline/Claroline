<?php

namespace Claroline\AuthenticationBundle\Listener\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AuthenticationBundle\Entity\ApiToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ApiTokenListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function preCreate(CreateEvent $event)
    {
        /** @var ApiToken $apiToken */
        $apiToken = $event->getObject();
        if (empty($apiToken->getUser())) {
            $apiToken->setUser($this->tokenStorage->getToken()->getUser());
        }
    }
}
