<?php

namespace Claroline\CoreBundle\Command;

use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanRecentWorkspacesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:recent_workspaces:clean')
            ->setDescription('Cleans the recent workspaces table of obsolete entries');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = ConsoleLogger::get($output);
        $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');
        $workspaceManager->setLogger($logger);
        $workspaceManager->cleanRecentWorkspaces();
    }
}
