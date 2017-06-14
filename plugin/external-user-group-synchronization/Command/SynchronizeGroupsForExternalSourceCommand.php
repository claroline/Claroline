<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 5/23/17
 */

namespace Claroline\ExternalSynchronizationBundle\Command;

use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SynchronizeGroupsForExternalSourceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:external_sync:groups')
            ->setDescription('Synchronize groups for a specific external source.');

        $this->setDefinition(
            [
                new InputArgument('source_slug', InputArgument::REQUIRED, 'The external source slug'),
            ]
        );

        $this->addOption(
            'force_unsubscribe',
            'f',
            InputOption::VALUE_NONE,
            'When set to true, unsubscribes users not present in distant group'
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = [
            'source_slug' => 'external source slug',
        ];

        foreach ($params as $argument => $argumentName) {
            if (!$input->getArgument($argument)) {
                $input->setArgument(
                    $argument, $this->askArgument($output, $argumentName)
                );
            }
        }
    }

    protected function askArgument(OutputInterface $output, $argumentName)
    {
        $argument = $this->getHelper('dialog')->askAndValidate(
            $output,
            "Enter the user {$argumentName}: ",
            function ($argument) {
                if (empty($argument)) {
                    throw new \Exception('This argument is required');
                }

                return $argument;
            }
        );

        return $argument;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceSlug = $input->getArgument('source_slug');
        $forceUnsubscribe = false;
        if ($input->getOption('force_unsubscribe')) {
            $forceUnsubscribe = true;
        }

        $externalSyncManager = $this->getContainer()->get('claroline.manager.external_user_group_sync_manager');
        $consoleLogger = ConsoleLogger::get($output);
        $externalSyncManager->setLogger($consoleLogger);
        $externalSyncManager->syncrhonizeAllGroupsForExternalSource($sourceSlug, $forceUnsubscribe);
    }
}
