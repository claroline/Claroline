<?php

namespace Claroline\CommunityBundle\Component\Log\Operational;

use Claroline\CoreBundle\Entity\User;
use Claroline\LogBundle\Component\Log\AbstractOperationalLog;

class LogUser extends AbstractOperationalLog
{
    public static function getName(): string
    {
        return 'user';
    }

    protected static function getEntityClass(): string
    {
        return User::class;
    }

    /** @param User $object */
    protected function getObjectName(object $object): string
    {
        return $object->getFullname();
    }
}
