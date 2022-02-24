<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SchedulerBundle\Command;

use Claroline\SchedulerBundle\Manager\ScheduledTaskManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ExecuteScheduledTasksCommand extends Command
{
    private $taskManager;
    private $messageBus;

    public function __construct(
        ScheduledTaskManager $taskManager,
        MessageBusInterface $messageBus
    ) {
        $this->taskManager = $taskManager;
        $this->messageBus = $messageBus;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Execute scheduled tasks with passed execution date');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Executing scheduled tasks...');
        $tasks = $this->taskManager->getTasksToExecute();

        foreach ($tasks as $task) {
            $output->writeln('['.$task->getAction().'] '.$task->getName().' : Requesting execution...');

            $this->taskManager->execute($task);
        }

        return 0;
    }
}
