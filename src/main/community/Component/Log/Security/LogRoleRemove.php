<?php

namespace Claroline\CommunityBundle\Component\Log\Security;

use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\RemoveRoleEvent;
use Claroline\LogBundle\Component\Log\AbstractSecurityLog;

class LogRoleRemove extends AbstractSecurityLog
{
    public static function getName(): string
    {
        return 'user.remove_role';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::REMOVE_ROLE => ['logRoleRemove', 10],
        ];
    }

    public function logRoleRemove(RemoveRoleEvent $event): void
    {
        foreach ($event->getUsers() as $user) {
            $this->log(
                $this->getTranslator()->trans('user.remove_role_message', [
                    '%user%' => $user->getFullName(),
                ], 'log'),
                $user->getId()
            );
        }
    }
}
