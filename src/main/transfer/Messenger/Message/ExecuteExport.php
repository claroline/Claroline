<?php

namespace Claroline\TransferBundle\Messenger\Message;

class ExecuteExport
{
    /** @var int */
    private $exportId;

    public function __construct(int $exportId)
    {
        $this->exportId = $exportId;
    }

    public function getExportId(): int
    {
        return $this->exportId;
    }
}
