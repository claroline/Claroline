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
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    private $manager;

    public function setManager(Manager $manager)
    {
        $this->manager = $manager;
    }

    protected function configure()
    {
        $this->addArgument('bundle', InputArgument::REQUIRED, 'The bundle name');
    }

    protected function getManager(OutputInterface $output)
    {
        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        ];
        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);
        $this->manager->setLogger($consoleLogger);

        return $this->manager;
    }

    protected function getTargetBundle(InputInterface $input)
    {
        return $this->getApplication()->getKernel()->getBundle(
            $input->getArgument('bundle')
        );
    }

    protected function getOutputBundle(InputInterface $input)
    {
        $bundleName = $input->getOption('output');

        if ($bundleName) {
            $bundles = $this->getApplication()->getKernel()->getBundle(
                $bundleName,
                false
            );

            foreach ($bundles as $bundle) {
                if ($bundle->getName() == $bundleName) {
                    return $bundle;
                }
            }
        }
    }
}
