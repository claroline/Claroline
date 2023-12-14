<?php

namespace Claroline\OpenBadgeBundle\Component\Log\Functional;

use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;
use Claroline\OpenBadgeBundle\Event\AddBadgeEvent;
use Claroline\OpenBadgeBundle\Event\BadgeEvents;

class LogBadgeGrant extends AbstractFunctionalLog
{
    public static function getName(): string
    {
        return 'badge.grant';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BadgeEvents::ADD_BADGE => ['logGrant', -25],
        ];
    }

    public function logGrant(AddBadgeEvent $event): void
    {
        $user = $event->getUser();
        $badge = $event->getBadge();

        $this->log(
            $this->getTranslator()->trans('badge.grant_message', [
                '%badge%' => $badge->getName(),
            ], 'log'),
            $badge->getWorkspace(),
            null,
            $user
        );
    }
}
