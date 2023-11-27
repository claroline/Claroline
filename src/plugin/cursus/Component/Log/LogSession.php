<?php

namespace Claroline\CursusBundle\Component\Log\Operational;

use Claroline\CursusBundle\Entity\Session;
use Claroline\LogBundle\Component\Log\AbstractOperationalLog;

class LogSession extends AbstractOperationalLog
{
    public static function getName(): string
    {
        return 'training_session';
    }

    protected static function getEntityClass(): string
    {
        return Session::class;
    }
}
