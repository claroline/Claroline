<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Task;

use Claroline\CoreBundle\Event\GenericDataEvent;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteScheduledTasksCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('claroline:tasks:execute')
            ->setDescription('Execute scheduled tasks with passed execution date');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Executing scheduled tasks...');
        $taskManager = $this->getContainer()->get('claroline.manager.scheduled_task_manager');
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $tasks = $taskManager->getTasksToExecute();

        foreach ($tasks as $task) {
            $output->writeln('['.$task->getType().'] '.$task->getName().' : Requesting execution...');
            $dispatcher->dispatch(
                'claroline_scheduled_task_execute_'.$task->getType(),
                new GenericDataEvent($task)
            );
        }
    }
}
