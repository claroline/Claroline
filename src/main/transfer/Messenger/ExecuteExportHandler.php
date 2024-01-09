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
use Claroline\TransferBundle\Entity\ExportFile;
use Claroline\TransferBundle\Manager\TransferManager;
use Claroline\TransferBundle\Messenger\Message\ExecuteExport;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ExecuteExportHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly TransferManager $transferManager
    ) {
    }

    public function __invoke(ExecuteExport $exportMessage): void
    {
        $exportFile = $this->om->getRepository(ExportFile::class)->find($exportMessage->getExportId());
        if (empty($exportFile)) {
            return;
        }

        $this->transferManager->export($exportFile);
    }
}
