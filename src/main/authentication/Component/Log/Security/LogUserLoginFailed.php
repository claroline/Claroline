<?php

namespace Claroline\AuthenticationBundle\Component\Log\Security;

use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AuthenticationFailureEvent;
use Claroline\LogBundle\Component\Log\AbstractSecurityLog;
use Claroline\LogBundle\Helper\ColorHelper;

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
            $this->getTranslator()->trans('authentication.user_login_failed_message', [
                '%user%' => $event->getUser(),
                '%error%' => ColorHelper::danger($this->getTranslator()->trans($event->getMessage(), [], 'security')),
            ], 'log'),
            $event->getUser()
        );
    }
}
