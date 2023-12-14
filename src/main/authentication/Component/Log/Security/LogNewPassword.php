<?php

namespace Claroline\AuthenticationBundle\Component\Log\Security;

use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\NewPasswordEvent;
use Claroline\LogBundle\Component\Log\AbstractSecurityLog;

class LogNewPassword extends AbstractSecurityLog
{
    public static function getName(): string
    {
        return 'authentication.new_password';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::NEW_PASSWORD => ['logNewPassword', 10],
        ];
    }

    public function logNewPassword(NewPasswordEvent $event): void
    {
        $this->log(
            $this->getTranslator()->trans('authentication.new_password_message', [
                '%user%' => $event->getUser(),
            ], 'log'),
            $event->getUser()
        );
    }
}
