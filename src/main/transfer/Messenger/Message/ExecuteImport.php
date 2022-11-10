<?php

namespace Claroline\TransferBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncLowMessageInterface;

class ExecuteImport implements AsyncLowMessageInterface
{
    /** @var int */
    private $importId;

    public function __construct(int $importId)
    {
        $this->importId = $importId;
    }

    public function getImportId(): int
    {
        return $this->importId;
    }
}
