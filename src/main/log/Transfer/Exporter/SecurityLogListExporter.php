<?php

namespace Claroline\LogBundle\Transfer\Exporter;

use Claroline\LogBundle\Entity\SecurityLog;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class SecurityLogListExporter extends AbstractListExporter
{
    public function getAction(): array
    {
        return ['log', 'security_log_list'];
    }

    protected static function getClass(): string
    {
        return SecurityLog::class;
    }
}
