<?php

namespace Claroline\AuthenticationBundle\Subscriber;

use Claroline\AuthenticationBundle\Security\Authentication\Authenticator;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AbstractRoleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RefreshTokenSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Authenticator $authenticator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::ADD_ROLE => 'onRoleChanges',
            SecurityEvents::REMOVE_ROLE => 'onRoleChanges',
        ];
    }

    /**
     * Checks if the roles of the current user have been changed and refreshes its token if needed.
     */
    public function onRoleChanges(AbstractRoleEvent $event): void
    {
        $updatedUsers = $event->getUsers();
        foreach ($updatedUsers as $updatedUser) {
            if ($this->authenticator->isAuthenticatedUser($updatedUser)) {
                $this->authenticator->createToken($updatedUser);

                break;
            }
        }
    }
}
