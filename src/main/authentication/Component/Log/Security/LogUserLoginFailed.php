<?php

namespace Claroline\AuthenticationBundle\Component\Log\Security;

use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AuthenticationFailureEvent;
use Claroline\LogBundle\Component\Log\AbstractSecurityLog;

class LogUserLoginFailed extends AbstractSecurityLog
{
    public static function getName(): string
    {
        return 'authentication.user_login_failed';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::AUTHENTICATION_FAILURE => ['logAuthenticationFailure', 10],
        ];
    }

    public function logAuthenticationFailure(AuthenticationFailureEvent $event): void
    {
        $this->log(
            $this->getTranslator()->trans('user_login_failed_message', [
                '%username%' => $event->getUser(),
            ], 'log'),
            $event->getUser()
        );
    }
}
