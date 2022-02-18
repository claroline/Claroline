<?php

namespace Claroline\LogBundle\Transfer\Exporter;

use Claroline\LogBundle\Entity\MessageLog;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class MessageLogListExporter extends AbstractListExporter
{
    public function getAction(): array
    {
        return ['log', 'message_log_list'];
    }

    protected static function getClass(): string
    {
        return MessageLog::class;
    }
}
