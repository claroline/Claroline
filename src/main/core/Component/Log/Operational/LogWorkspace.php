<?php

namespace Claroline\CoreBundle\Component\Log\Operational;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\LogBundle\Component\Log\AbstractOperationalLog;

class LogWorkspace extends AbstractOperationalLog
{
    public static function getName(): string
    {
        return 'workspace';
    }

    protected static function getEntityClass(): string
    {
        return Workspace::class;
    }
}
