<?php

namespace Claroline\OpenBadgeBundle\Component\Log\Functional;

use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;
use Claroline\OpenBadgeBundle\Event\BadgeEvents;
use Claroline\OpenBadgeBundle\Event\RemoveBadgeEvent;

class LogBadgeRevoke extends AbstractFunctionalLog
{
    public static function getName(): string
    {
        return 'badge.revoke';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BadgeEvents::REMOVE_BADGE => ['logRevoke', -25],
        ];
    }

    public function logRevoke(RemoveBadgeEvent $event): void
    {
        $user = $event->getUser();
        $badge = $event->getBadge();

        $this->log(
            $this->getTranslator()->trans('badge.revoke_message', [
                '%badge%' => $badge->getName(),
            ], 'log'),
            $badge->getWorkspace(),
            null,
            $user
        );
    }
}
