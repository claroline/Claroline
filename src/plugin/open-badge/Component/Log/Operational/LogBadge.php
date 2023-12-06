<?php

namespace Claroline\OpenBadgeBundle\Component\Log\Operational;

use Claroline\LogBundle\Component\Log\AbstractOperationalLog;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;

class LogBadge extends AbstractOperationalLog
{
    public static function getName(): string
    {
        return 'badge';
    }

    protected static function getEntityClass(): string
    {
        return BadgeClass::class;
    }
}
