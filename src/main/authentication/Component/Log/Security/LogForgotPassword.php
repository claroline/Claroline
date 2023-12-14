<?php

namespace Claroline\AuthenticationBundle\Component\Log\Security;

use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\ForgotPasswordEvent;
use Claroline\LogBundle\Component\Log\AbstractSecurityLog;

class LogForgotPassword extends AbstractSecurityLog
{
    public static function getName(): string
    {
        return 'authentication.forgot_password';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::FORGOT_PASSWORD => ['logForgotPassword', 10],
        ];
    }

    public function logForgotPassword(ForgotPasswordEvent $event): void
    {
        $this->log(
            $this->getTranslator()->trans('authentication.forgot_password_message', [
                '%user%' => $event->getUser(),
            ], 'log'),
            $event->getUser()
        );
    }
}
