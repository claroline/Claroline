<?php

namespace Claroline\AuthenticationBundle\Component\Log\Security;

use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\UserLoginEvent;
use Claroline\LogBundle\Component\Log\AbstractSecurityLog;

class LogUserLogin extends AbstractSecurityLog
{
    public static function getName(): string
    {
        return 'authentication.user_login';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::USER_LOGIN => ['logUserLogin', 10],
        ];
    }

    public function logUserLogin(UserLoginEvent $loginEvent): void
    {
        $this->log(
            $this->getTranslator()->trans('authentication.user_login_message', [
                '%user%' => $loginEvent->getUser(),
            ], 'log'),
            $loginEvent->getUser()
        );
    }
}
