<?php

namespace Claroline\HistoryBundle\Command;

use Claroline\AppBundle\Logger\ConsoleLogger;
use Claroline\HistoryBundle\Manager\HistoryManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanHistoryCommand extends Command
{
    private $historyManager;

    public function __construct(HistoryManager $historyManager)
    {
        $this->historyManager = $historyManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Cleans the recent workspaces and resources table of obsolete entries');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = ConsoleLogger::get($output);

        $this->historyManager->setLogger($logger);
        $this->historyManager->cleanRecent();

        return 0;
    }
}
