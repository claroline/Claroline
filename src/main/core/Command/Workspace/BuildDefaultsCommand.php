<?php

namespace Claroline\CoreBundle\Command\Workspace;

use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildDefaultsCommand extends Command
{
    public function __construct(private readonly WorkspaceManager $workspaceManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('This command allows you to rebuild the default workspaces');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->workspaceManager->getDefaultModel(false, true);
        $this->workspaceManager->getDefaultModel(true, true);

        return 0;
    }
}
