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

use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->addArgument('bundle', InputArgument::REQUIRED, 'The bundle name');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('bundle')) {
            $bundleName = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Enter the bundle name: ',
                function ($argument) {
                    if (empty($argument)) {
                        throw new \Exception('This argument is required');
                    }

                    return $argument;
                }
            );
            $input->setArgument('bundle', $bundleName);
        }
    }

    protected function getManager(OutputInterface $output)
    {
        $manager = $this->getContainer()->get('claroline.migration.manager');
        $verbosityLevelMap = array(
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        );
        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);
        $manager->setLogger($consoleLogger);

        return $manager;
    }

    protected function getTargetBundle(InputInterface $input)
    {
        $bundleName = $input->getArgument('bundle');
        $bundles = $this->getContainer()->get('kernel')->getBundle(
            $bundleName,
            false
        );

        foreach ($bundles as $bundle) {
            if ($bundle->getName() == $bundleName) {
                return $bundle;
            }
        }
    }

    protected function getOutputBundle(InputInterface $input)
    {
        $bundleName = $input->getOption('output');

        if ($bundleName) {
            $bundles = $this->getContainer()->get('kernel')->getBundle(
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
