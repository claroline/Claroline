<?php

namespace Claroline\CommunityBundle\Component\Log\Operational;

use Claroline\CoreBundle\Entity\Group;
use Claroline\LogBundle\Component\Log\AbstractOperationalLog;

class LogGroup extends AbstractOperationalLog
{
    public static function getName(): string
    {
        return 'group';
    }

    protected static function getEntityClass(): string
    {
        return Group::class;
    }
}
