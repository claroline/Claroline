<?php

namespace Claroline\CoreBundle\Component\Log\Operational;

use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\LogBundle\Component\Log\AbstractOperationalLog;

class LogTool extends AbstractOperationalLog
{
    public static function getName(): string
    {
        return 'tool';
    }

    protected static function getEntityClass(): string
    {
        return OrderedTool::class;
    }
}
