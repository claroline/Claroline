<?php

namespace Claroline\CommunityBundle\Component\Log\Operational;

use Claroline\CoreBundle\Entity\Role;
use Claroline\LogBundle\Component\Log\AbstractOperationalLog;

class LogRole extends AbstractOperationalLog
{
    public static function getName(): string
    {
        return 'role';
    }

    protected static function getEntityClass(): string
    {
        return Role::class;
    }
}
