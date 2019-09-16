<?php

namespace Claroline\HistoryBundle\Command;

use Claroline\AppBundle\Logger\ConsoleLogger;
use Claroline\HistoryBundle\Manager\HistoryManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanHistoryCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:history:clean')
            ->setDescription('Cleans the recent workspaces and resources table of obsolete entries');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = ConsoleLogger::get($output);

        /** @var HistoryManager $historyManager */
        $historyManager = $this->getContainer()->get('claroline.manager.history');
        $historyManager->setLogger($logger);
        $historyManager->cleanRecent();
    }
}
