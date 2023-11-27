<?php

namespace Claroline\CommunityBundle\Component\Log\Operational;

use Claroline\CommunityBundle\Entity\Team;
use Claroline\LogBundle\Component\Log\AbstractOperationalLog;

class LogTeam extends AbstractOperationalLog
{
    public static function getName(): string
    {
        return 'team';
    }

    protected static function getEntityClass(): string
    {
        return Team::class;
    }
}
