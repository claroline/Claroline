<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Workspace;

use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends Command
{
    private $workspaceManager;

    public function __construct(WorkspaceManager $workspaceManager)
    {
        $this->workspaceManager = $workspaceManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create a workspace from a zip archive')
            ->setDefinition([
                new InputArgument('path', InputArgument::REQUIRED, 'The absolute path to the zip file.'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->workspaceManager->import($input->getArgument('path'));

        return 0;
    }
}
