<?php

namespace Claroline\AuthenticationBundle\Subscriber;

use Claroline\AuthenticationBundle\Security\Authentication\Authenticator;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AbstractRoleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RefreshTokenSubscriber implements EventSubscriberInterface
{
    /** @var Authenticator */
    private $authenticator;

    public function __construct(
        Authenticator $authenticator
    ) {
        $this->authenticator = $authenticator;
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
    public function onRoleChanges(AbstractRoleEvent $event)
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
