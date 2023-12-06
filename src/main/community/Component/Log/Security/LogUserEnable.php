<?php

namespace Claroline\CommunityBundle\Component\Log\Security;

use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\UserEnableEvent;
use Claroline\LogBundle\Component\Log\AbstractSecurityLog;

class LogUserEnable extends AbstractSecurityLog
{
    public static function getName(): string
    {
        return 'user.enable';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::USER_ENABLE => ['logUserEnable', 10],
        ];
    }

    public function logUserEnable(UserEnableEvent $event): void
    {
        $this->log(
            $this->getTranslator()->trans('user.enable_message', [
                '%user%' => $event->getUser()->getFullName(),
            ], 'log'),
            $event->getUser()
        );
    }
}
