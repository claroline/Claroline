<?php

namespace Claroline\TransferBundle\Messenger\Message;

class ExecuteImport
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
