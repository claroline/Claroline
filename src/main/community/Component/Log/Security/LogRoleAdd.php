<?php

namespace Claroline\CommunityBundle\Component\Log\Security;

use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AddRoleEvent;
use Claroline\LogBundle\Component\Log\AbstractSecurityLog;

class LogRoleAdd extends AbstractSecurityLog
{
    public static function getName(): string
    {
        return 'user.add_role';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::ADD_ROLE => ['logRoleAdd', 10],
        ];
    }

    public function logRoleAdd(AddRoleEvent $event): void
    {
        $role = $event->getRole();

        $roleName = $this->getTranslator()->trans($role->getTranslationKey(), [], 'platform');
        if ($role->getWorkspace()) {
            $roleName .= ' ('.$role->getWorkspace()->getName().')';
        }

        foreach ($event->getUsers() as $user) {
            $this->log(
                $this->getTranslator()->trans('user.add_role_message', [
                    '%role%' => $roleName,
                    '%user%' => $user->getFullName(),
                ], 'log'),
                $user
            );
        }
    }
}
