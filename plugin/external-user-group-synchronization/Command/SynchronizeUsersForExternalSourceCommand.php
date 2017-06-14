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
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SynchronizeUsersForExternalSourceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:external_sync:users')
            ->setDescription('Synchronize users for a specific external source.');

        $this->setDefinition(
            [
                new InputArgument('source_slug', InputArgument::REQUIRED, 'The external source slug'),
                new InputArgument(
                    'cas_field',
                    InputArgument::OPTIONAL,
                    'The CAS user field to user for sync',
                    'username'
                ),
            ]
        );

        $this->addOption(
            'cas',
            'c',
            InputOption::VALUE_NONE,
            'When set to true, also syncrhronize CAS users'
        );

        $this->addOption(
            'wsc',
            'w',
            InputOption::VALUE_NONE,
            'When set to true, subscribe users to WS_CREATOR role'
        );

        $this->addOption(
            'admin',
            'a',
            InputOption::VALUE_NONE,
            'When set to true, subscribe users to ADMIN role'
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
        $casField = $input->getArgument('cas_field');
        $cas = false;
        if ($input->getOption('cas')) {
            $cas = true;
        }

        $additionalRole = null;
        $additionalRoleName = null;
        if ($input->getOption('admin')) {
            $additionalRoleName = PlatformRoles::ADMIN;
        }
        if ($input->getOption('wsc')) {
            $additionalRoleName = PlatformRoles::WS_CREATOR;
        }

        if (!is_null($additionalRoleName)) {
            $roleManager = $this->getContainer()->get('claroline.manager.role_manager');
            $additionalRole = $roleManager->getRoleByName($additionalRoleName);
        }

        $externalSyncManager = $this->getContainer()->get('claroline.manager.external_user_group_sync_manager');
        $consoleLogger = ConsoleLogger::get($output);
        $externalSyncManager->setLogger($consoleLogger);
        $externalSyncManager->synchronizeUsersForExternalSource($sourceSlug, $cas, $casField, $additionalRole);
    }
}
