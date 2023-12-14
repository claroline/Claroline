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
        $role = $event->getRole();

        $roleName = $this->getTranslator()->trans($role->getTranslationKey(), [], 'platform');
        if ($role->getWorkspace()) {
            $roleName .= ' ('.$role->getWorkspace()->getName().')';
        }

        foreach ($event->getUsers() as $user) {
            $this->log(
                $this->getTranslator()->trans('user.remove_role_message', [
                    '%role%' => $roleName,
                    '%user%' => $user->getFullName(),
                ], 'log'),
                $user
            );
        }
    }
}
