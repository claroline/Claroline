<?php

namespace Claroline\AuthenticationBundle\Component\Log\Security;

use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\LogBundle\Component\Log\AbstractSecurityLog;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;

class LogUserSwitch extends AbstractSecurityLog
{
    public static function getName(): string
    {
        return 'authentication.user_switch';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::SWITCH_USER => ['logSwitchUser', 10],
        ];
    }

    public function logSwitchUser(SwitchUserEvent $event): void
    {
        $this->log(
            $this->getTranslator()->trans('user_switch_message', [
                '%username%' => $event->getTargetUser(),
            ], 'log'),
            $event->getTargetUser()
        );
    }
}
