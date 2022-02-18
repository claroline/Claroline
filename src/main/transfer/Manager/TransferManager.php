<?php

namespace Claroline\TransferBundle\Manager;

use Claroline\TransferBundle\Entity\AbstractTransferFile;
use Claroline\TransferBundle\Entity\ExportFile;
use Claroline\TransferBundle\Entity\ImportFile;
use Claroline\TransferBundle\Messenger\Message\ExecuteExport;
use Claroline\TransferBundle\Messenger\Message\ExecuteImport;
use Symfony\Component\Messenger\MessageBusInterface;

class TransferManager
{
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var string */
    private $logDir;

    public function __construct(
        MessageBusInterface $messageBus,
        string $logDir
    ) {
        $this->messageBus = $messageBus;
        $this->logDir = $logDir;
    }

    public function getLog(AbstractTransferFile $transferFile): ?string
    {
        $logFile = $this->logDir.DIRECTORY_SEPARATOR.$transferFile->getLog().'.json';
        if (file_exists($logFile)) {
            return file_get_contents($logFile);
        }

        return null;
    }

    public function import(ImportFile $importFile)
    {
        $this->messageBus->dispatch(new ExecuteImport($importFile->getId()));
    }

    public function export(ExportFile $exportFile)
    {
        $this->messageBus->dispatch(new ExecuteExport($exportFile->getId()));
    }
}
