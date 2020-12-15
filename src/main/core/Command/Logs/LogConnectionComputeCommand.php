<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Logs;

use Claroline\CoreBundle\Manager\LogConnectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LogConnectionComputeCommand extends Command
{
    private $logConnectManager;

    public function __construct(LogConnectManager $logConnectManager)
    {
        $this->logConnectManager = $logConnectManager;

        parent::__construct();
    }

    protected function configure()
    {
        parent::configure();

        $this->setName('claroline:connection:duration')
            ->setDescription('Computes duration for connection logs');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>  Computing spent time in platform...</info>');
        $this->logConnectManager->computeAllPlatformDuration();
        $output->writeln('<info>  Spent time in platform computed.</info>');

        $output->writeln('<info>  Computing spent time in workspaces...</info>');
        $this->logConnectManager->computeAllWorkspacesDuration();
        $output->writeln('<info>  Spent time in workspaces computed.</info>');

        $output->writeln('<info>  Computing spent time in admin tools...</info>');
        $this->logConnectManager->computeAllAdminToolsDuration();
        $output->writeln('<info>  Spent time in admin tools computed.</info>');

        $output->writeln('<info>  Computing spent time in tools...</info>');
        $this->logConnectManager->computeAllToolsDuration();
        $output->writeln('<info>  Spent time in tools computed.</info>');

        $output->writeln('<info>  Computing spent time in resources...</info>');
        $this->logConnectManager->computeAllResourcesDuration();
        $output->writeln('<info>  Spent time in resources computed.</info>');

        return 0;
    }
}
