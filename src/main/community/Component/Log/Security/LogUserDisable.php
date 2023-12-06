<?php

namespace Claroline\CommunityBundle\Component\Log\Security;

use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\UserDisableEvent;
use Claroline\LogBundle\Component\Log\AbstractSecurityLog;

class LogUserDisable extends AbstractSecurityLog
{
    public static function getName(): string
    {
        return 'user.disable';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::USER_DISABLE => ['logUserDisable', 10],
        ];
    }

    public function logUserDisable(UserDisableEvent $event): void
    {
        $this->log(
            $this->getTranslator()->trans('user.disable_message', [
                '%user%' => $event->getUser()->getFullName(),
            ], 'log'),
            $event->getUser()
        );
    }
}
