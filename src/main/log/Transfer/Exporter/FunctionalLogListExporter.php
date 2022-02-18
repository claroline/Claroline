<?php

namespace Claroline\LogBundle\Transfer\Exporter;

use Claroline\LogBundle\Entity\FunctionalLog;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class FunctionalLogListExporter extends AbstractListExporter
{
    public function getAction(): array
    {
        return ['log', 'functional_log_list'];
    }

    protected static function getClass(): string
    {
        return FunctionalLog::class;
    }
}
