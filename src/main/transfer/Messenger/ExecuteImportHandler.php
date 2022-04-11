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
use Claroline\TransferBundle\Manager\TransferManager;
use Claroline\TransferBundle\Messenger\Message\ExecuteImport;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ExecuteImportHandler implements MessageHandlerInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var TransferManager */
    private $transferManager;

    public function __construct(
        ObjectManager $om,
        TransferManager $transferManager
    ) {
        $this->om = $om;
        $this->transferManager = $transferManager;
    }

    public function __invoke(ExecuteImport $importMessage)
    {
        $importFile = $this->om->getRepository(ImportFile::class)->find($importMessage->getImportId());
        if (empty($importFile) || empty($importFile->getFile())) {
            return;
        }

        try {
            $execStatus = $this->transferManager->import($importFile);
            $failed = TransferFileInterface::ERROR === $execStatus;
        } catch (\Exception $e) {
            $failed = true;
        }

        if ($failed) {
            throw new UnrecoverableMessageHandlingException();
        }
    }
}
