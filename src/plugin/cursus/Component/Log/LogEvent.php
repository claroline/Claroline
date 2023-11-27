<?php

namespace Claroline\CursusBundle\Component\Log\Operational;

use Claroline\CursusBundle\Entity\Event;
use Claroline\LogBundle\Component\Log\AbstractOperationalLog;

class LogEvent extends AbstractOperationalLog
{
    public static function getName(): string
    {
        return 'training_event';
    }

    protected static function getEntityClass(): string
    {
        return Event::class;
    }
}
