<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TransferBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\TransferBundle\Entity\ImportFile;
use Claroline\TransferBundle\Entity\TransferFileInterface;
use Claroline\TransferBundle\Manager\ImportManager;
use Claroline\TransferBundle\Messenger\Message\ExecuteImport;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class ExecuteImportHandler
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly ImportManager $importManager
    ) {
    }

    public function __invoke(ExecuteImport $importMessage): void
    {
        $importFile = $this->om->getRepository(ImportFile::class)->find($importMessage->getImportId());
        if (empty($importFile) || empty($importFile->getFile())) {
            return;
        }

        try {
            $execStatus = $this->importManager->import($importFile);
            $failed = TransferFileInterface::ERROR === $execStatus;
        } catch (\Exception $e) {
            $failed = true;
        }

        if ($failed) {
            throw new UnrecoverableMessageHandlingException();
        }
    }
}
