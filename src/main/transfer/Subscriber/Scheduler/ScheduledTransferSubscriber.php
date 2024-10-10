<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TransferBundle\Subscriber\Scheduler;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\SchedulerBundle\Event\ExecuteScheduledTaskEvent;
use Claroline\TransferBundle\Entity\ExportFile;
use Claroline\TransferBundle\Entity\ImportFile;
use Claroline\TransferBundle\Manager\ExportManager;
use Claroline\TransferBundle\Manager\ImportManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ScheduledTransferSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly ImportManager $importManager,
        private readonly ExportManager $exportManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'scheduler.execute.export' => 'executeExport',
            'scheduler.execute.import' => 'executeImport',
        ];
    }

    public function executeExport(ExecuteScheduledTaskEvent $event): void
    {
        $task = $event->getTask();
        if (empty($task->getParentId())) {
            return;
        }

        $exportFile = $this->om->getRepository(ExportFile::class)->findOneBy(['uuid' => $task->getParentId()]);
        if (empty($exportFile)) {
            return;
        }

        // we don't ask for a Messenger message here because scheduled tasks are already executed in one
        $status = $this->exportManager->export($exportFile);

        $event->setStatus($status);
    }

    public function executeImport(ExecuteScheduledTaskEvent $event): void
    {
        $task = $event->getTask();
        if (empty($task->getParentId())) {
            return;
        }

        $importFile = $this->om->getRepository(ImportFile::class)->findOneBy(['uuid' => $task->getParentId()]);
        if (empty($importFile)) {
            return;
        }

        // we don't ask for a Messenger message here because scheduled tasks are already executed in one
        $status = $this->importManager->import($importFile);

        $event->setStatus($status);
    }
}
