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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LogConnectionComputeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('claroline:connection:duration')
            ->setDescription('Computes duration for connection logs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var LogConnectManager $logConnectManager */
        $logConnectManager = $this->getContainer()->get('claroline.manager.log_connect');

        $output->writeln('<info>  Computing spent time in platform...</info>');
        $logConnectManager->computeAllPlatformDuration();
        $output->writeln('<info>  Spent time in platform computed.</info>');

        $output->writeln('<info>  Computing spent time in workspaces...</info>');
        $logConnectManager->computeAllWorkspacesDuration();
        $output->writeln('<info>  Spent time in workspaces computed.</info>');

        $output->writeln('<info>  Computing spent time in admin tools...</info>');
        $logConnectManager->computeAllAdminToolsDuration();
        $output->writeln('<info>  Spent time in admin tools computed.</info>');

        $output->writeln('<info>  Computing spent time in tools...</info>');
        $logConnectManager->computeAllToolsDuration();
        $output->writeln('<info>  Spent time in tools computed.</info>');

        $output->writeln('<info>  Computing spent time in resources...</info>');
        $logConnectManager->computeAllResourcesDuration();
        $output->writeln('<info>  Spent time in resources computed.</info>');
    }
}
