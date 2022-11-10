<?php

namespace Claroline\TransferBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncLowMessageInterface;

class ExecuteExport implements AsyncLowMessageInterface
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
