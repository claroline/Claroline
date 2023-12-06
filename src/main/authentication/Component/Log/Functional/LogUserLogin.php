<?php

namespace Claroline\AuthenticationBundle\Component\Log\Functional;

use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\UserLoginEvent;
use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;

class LogUserLogin extends AbstractFunctionalLog
{
    public static function getName(): string
    {
        return 'authentication.user_login';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::USER_LOGIN => ['logUserLogin', -25],
        ];
    }

    public function logUserLogin(UserLoginEvent $loginEvent): void
    {
        $this->log(
            $this->getTranslator()->trans('user_login_message', [
                '%username%' => $loginEvent->getUser(),
            ], 'log')
        );
    }
}
