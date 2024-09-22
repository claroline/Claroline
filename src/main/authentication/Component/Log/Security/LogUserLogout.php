<?php

namespace Claroline\AuthenticationBundle\Component\Log\Security;

use Claroline\CoreBundle\Entity\User;
use Claroline\LogBundle\Component\Log\AbstractSecurityLog;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogUserLogout extends AbstractSecurityLog
{
    public static function getName(): string
    {
        return 'authentication.user_logout';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => ['logUserLogout', 10],
        ];
    }

    public function logUserLogout(LogoutEvent $event): void
    {
        if (!$event->getToken()?->getUser() instanceof User) {
            return;
        }

        $this->log(
            $this->getTranslator()->trans('authentication.user_logout_message', [
                '%user%' => $event->getToken()?->getUser(),
            ], 'log'),
            $event->getToken()?->getUser()
        );
    }
}
