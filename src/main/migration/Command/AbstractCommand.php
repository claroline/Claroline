<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MigrationBundle\Command;

use Claroline\MigrationBundle\Manager\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

abstract class AbstractCommand extends Command
{
    private Manager $manager;

    public function setManager(Manager $manager): void
    {
        $this->manager = $manager;
    }

    protected function configure(): void
    {
        $this->addArgument('bundle', InputArgument::REQUIRED, 'The bundle name');
    }

    protected function getManager(OutputInterface $output): Manager
    {
        $this->manager->setLogger(new ConsoleLogger($output));

        return $this->manager;
    }

    protected function getTargetBundle(InputInterface $input): BundleInterface
    {
        return $this->getApplication()->getKernel()->getBundle(
            $input->getArgument('bundle')
        );
    }
}
