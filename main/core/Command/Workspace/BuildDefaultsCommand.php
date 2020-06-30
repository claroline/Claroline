<?php

namespace Claroline\CoreBundle\Command\Workspace;

use Claroline\AppBundle\Logger\ConsoleLogger;
use Claroline\CoreBundle\Command\AdminCliCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildDefaultsCommand extends ContainerAwareCommand implements AdminCliCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspace:build-default')
            ->setDescription('This command allow you to rebuild the default workspaces');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = ConsoleLogger::get($output);
        $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');
        $workspaceManager->setLogger($consoleLogger);
        $workspaceManager->getDefaultModel(false, true);
        $workspaceManager->getDefaultModel(true, true);
    }
}
