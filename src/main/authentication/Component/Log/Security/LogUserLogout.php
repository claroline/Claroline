<?php

namespace Claroline\AuthenticationBundle\Component\Log\Security;

use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\UserLogoutEvent;
use Claroline\LogBundle\Component\Log\AbstractSecurityLog;

class LogUserLogout extends AbstractSecurityLog
{
    public static function getName(): string
    {
        return 'authentication.user_logout';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::USER_LOGOUT => ['logUserLogout', 10],
        ];
    }

    public function logUserLogout(UserLogoutEvent $loginEvent): void
    {
        $this->log(
            $this->getTranslator()->trans('user_logout_message', [
                '%username%' => $loginEvent->getUser(),
            ], 'log'),
            $loginEvent->getUser()
        );
    }
}
