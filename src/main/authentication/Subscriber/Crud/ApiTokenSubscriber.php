<?php

namespace Claroline\AuthenticationBundle\Subscriber\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AuthenticationBundle\Entity\ApiToken;
use Claroline\AppBundle\Event\CrudEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ApiTokenSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, ApiToken::class) => 'preCreate',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var ApiToken $apiToken */
        $apiToken = $event->getObject();
        if (empty($apiToken->getUser())) {
            $apiToken->setUser($this->tokenStorage->getToken()?->getUser());
        }
    }
}
