<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FunctionsCsvCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:csv:functions')
            ->setDescription('Executes multiple functions from a csv file');
        $this->setDefinition(
            array(
                new InputArgument(
                    'functions_csv_path',
                    InputArgument::REQUIRED,
                    'The absolute path to the csv file.'
                ),
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'functions_csv_path' => 'Absolute path to the csv file: ',
        );

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
            $argumentName,
            function ($argument) {

                if (empty($argument)) {
                    throw new \Exception('This argument is required');
                }

                return $argument;
            }
        );

        return $argument;
    }

    /**
     * All actions defined in CSV files are group by type and executed in the following order.
     *
     * Functions order :
     *     - Deletes users --> claro_delete_user
     *     - Creates groups --> claro_create_group
     *     - Empties group --> claro_empty_group
     *     - Deletes groups --> claro_delete_group
     *     - Creates users --> claro_create_user
     *     - [Forced flush]
     *     - Creates Workspaces --> claro_create_workspace
     *     - [Forced flush]
     *     - Deletes workspace roles --> claro_delete_workspace_role
     *     - Creates workspace roles --> claro_create_workspace_role
     *     - [Forced flush]
     *     - Unregisters users from groups --> claro_unregister_from_group
     *     - Unregisters users from workspaces (unregisters from workspace role) --> claro_unregister_user_from_workspace
     *     - Unregisters groups from workspaces (unregisters from workspace role) --> claro_unregister_group_from_workspace
     *     - [Forced flush]
     *     - Registers users to groups --> claro_register_to_group
     *     - Registers users to workspaces (registers to workspace role) --> claro_register_user_to_workspace
     *     - Registers groups to workspaces (registers to workspace role) --> claro_register_group_to_workspace
     *
     * Syntax : (Elements within [] are required. Elements within {} are optional)
     *
     *     - Creates users :
     *         [first name];[last name];[username];[password];[email];{code};{phone};{auth};{model name};claro_create_user
     *
     *     - Deletes users :
     *         [username];claro_delete_user
     *
     *     - Creates Workspaces :
     *         [name];[code];[isVisible];[selfRegistration];[registrationValidation];[selfUnregistration];[creator username];{model name};claro_create_workspace
     *
     *     - Creates groups :
     *         [group name];claro_create_group
     *
     *     - Empties group :
     *         [group name];claro_empty_group
     *
     *     - Deletes groups :
     *         [group name];claro_delete_group
     *
     *     - Creates workspace roles :
     *         [workspace code];[role name];claro_create_workspace_role
     *
     *     - Deletes workspace roles :
     *         [workspace code];[role name];claro_delete_workspace_role
     *
     *     - Registers users to groups :
     *         [username];[group name];claro_unregister_from_group
     *
     *     - Unregisters users from groups :
     *         [username];[group name];claro_register_to_group
     *
     *     - Registers users to workspaces (registers to workspace role) :
     *         [username];[workspace code];[role name];claro_register_user_to_workspace
     *
     *     - Unregisters users from workspaces (unregisters from workspace role) :
     *         [username];[workspace code];[role name];claro_unregister_user_from_workspace
     *
     *     - Registers groups to workspaces (registers to workspace role) :
     *         [group name];[workspace code];[role name];claro_register_group_to_workspace
     *
     *     - Unregisters groups from workspaces (unregisters from workspace role) :
     *         [group name];[workspace code];[role name];claro_unregister_group_from_workspace
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $importCsvManager = $this->getContainer()->get('claroline.manager.import_csv_manager');
        $file = $input->getArgument('functions_csv_path');
        $lines = str_getcsv(file_get_contents($file), PHP_EOL);
        $parsedDatas = $importCsvManager->parseCSVLines($lines);

        $om->startFlushSuite();

        if (isset($parsedDatas['claro_delete_user'])) {
            $output->writeln('------------------');
            $output->writeln('|  DELETE USERS  |');
            $output->writeln('------------------');
            $logs = $importCsvManager->manageUserDeletion(
                $parsedDatas['claro_delete_user']
            );

            foreach ($logs as $log) {
                $output->writeln($log);
            }
            $output->writeln('');
        }
        $om->forceFlush();

        if (isset($parsedDatas['claro_create_group'])) {
            $output->writeln('-------------------');
            $output->writeln('|  CREATE GROUPS  |');
            $output->writeln('-------------------');
            $logs = $importCsvManager->manageGroupCreation(
                $parsedDatas['claro_create_group']
            );

            foreach ($logs as $log) {
                $output->writeln($log);
            }
            $output->writeln('');
        }
        $om->forceFlush();

        if (isset($parsedDatas['claro_empty_group'])) {
            $output->writeln('------------------');
            $output->writeln('|  EMPTY GROUPS  |');
            $output->writeln('------------------');
            $logs = $importCsvManager->manageGroupEmptying(
                $parsedDatas['claro_empty_group']
            );

            foreach ($logs as $log) {
                $output->writeln($log);
            }
            $output->writeln('');
        }
        $om->forceFlush();

        if (isset($parsedDatas['claro_delete_group'])) {
            $output->writeln('-------------------');
            $output->writeln('|  DELETE GROUPS  |');
            $output->writeln('-------------------');
            $logs = $importCsvManager->manageGroupDeletion(
                $parsedDatas['claro_delete_group']
            );

            foreach ($logs as $log) {
                $output->writeln($log);
            }
            $output->writeln('');
        }
        $om->forceFlush();

        if (isset($parsedDatas['claro_create_user'])) {
            $output->writeln('------------------');
            $output->writeln('|  CREATE USERS  |');
            $output->writeln('------------------');
            $logs = $importCsvManager->manageUserCreation(
                $parsedDatas['claro_create_user']
            );

            foreach ($logs as $log) {
                $output->writeln($log);
            }
            $output->writeln('');
        }
        $om->forceFlush();

        if (isset($parsedDatas['claro_create_workspace'])) {
            $output->writeln('----------------------');
            $output->writeln('|  CREATE WORKSPACE  |');
            $output->writeln('----------------------');
            $logs = $importCsvManager->manageWorkspaceCreation(
                $parsedDatas['claro_create_workspace']
            );

            foreach ($logs as $log) {
                $output->writeln($log);
            }
            $output->writeln('');
        }
        $om->forceFlush();

        if (isset($parsedDatas['claro_delete_workspace_role'])) {
            $output->writeln('----------------------------');
            $output->writeln('|  DELETE WORKSPACE ROLES  |');
            $output->writeln('----------------------------');
            $logs = $importCsvManager->manageWorkspaceRoleDeletion(
                $parsedDatas['claro_delete_workspace_role']
            );

            foreach ($logs as $log) {
                $output->writeln($log);
            }
            $output->writeln('');
        }
        $om->forceFlush();

        if (isset($parsedDatas['claro_create_workspace_role'])) {
            $output->writeln('----------------------------');
            $output->writeln('|  CREATE WORKSPACE ROLES  |');
            $output->writeln('----------------------------');
            $logs = $importCsvManager->manageWorkspaceRoleCreation(
                $parsedDatas['claro_create_workspace_role']
            );

            foreach ($logs as $log) {
                $output->writeln($log);
            }
            $output->writeln('');
        }
        $om->forceFlush();

        if (isset($parsedDatas['claro_unregister_from_group'])) {
            $output->writeln('----------------------------------');
            $output->writeln('|  UNREGISTER USERS FROM GROUPS  |');
            $output->writeln('----------------------------------');
            $logs = $importCsvManager->manageGroupUnregistration(
                $parsedDatas['claro_unregister_from_group']
            );

            foreach ($logs as $log) {
                $output->writeln($log);
            }
            $output->writeln('');
        }
        $om->forceFlush();

        if (isset($parsedDatas['claro_unregister_user_from_workspace'])) {
            $output->writeln('--------------------------------------');
            $output->writeln('|  UNREGISTER USERS FROM WORKSPACES  |');
            $output->writeln('--------------------------------------');
            $logs = $importCsvManager->manageWorkspaceUnregistration(
                $parsedDatas['claro_unregister_user_from_workspace']
            );

            foreach ($logs as $log) {
                $output->writeln($log);
            }
            $output->writeln('');
        }
        $om->forceFlush();

        if (isset($parsedDatas['claro_unregister_group_from_workspace'])) {
            $output->writeln('---------------------------------------');
            $output->writeln('|  UNREGISTER GROUPS FROM WORKSPACES  |');
            $output->writeln('---------------------------------------');
            $logs = $importCsvManager->manageWorkspaceGroupUnregistration(
                $parsedDatas['claro_unregister_group_from_workspace']
            );

            foreach ($logs as $log) {
                $output->writeln($log);
            }
            $output->writeln('');
        }
        $om->forceFlush();

        if (isset($parsedDatas['claro_register_to_group'])) {
            $output->writeln('------------------------------');
            $output->writeln('|  REGISTER USERS TO GROUPS  |');
            $output->writeln('------------------------------');
            $logs = $importCsvManager->manageGroupRegistration(
                $parsedDatas['claro_register_to_group']
            );

            foreach ($logs as $log) {
                $output->writeln($log);
            }
            $output->writeln('');
        }
        $om->forceFlush();

        if (isset($parsedDatas['claro_register_user_to_workspace'])) {
            $output->writeln('----------------------------------');
            $output->writeln('|  REGISTER USERS TO WORKSPACES  |');
            $output->writeln('----------------------------------');
            $logs = $importCsvManager->manageWorkspaceRegistration(
                $parsedDatas['claro_register_user_to_workspace']
            );

            foreach ($logs as $log) {
                $output->writeln($log);
            }
            $output->writeln('');
        }
        $om->forceFlush();

        if (isset($parsedDatas['claro_register_group_to_workspace'])) {
            $output->writeln('-----------------------------------');
            $output->writeln('|  REGISTER GROUPS TO WORKSPACES  |');
            $output->writeln('-----------------------------------');
            $logs = $importCsvManager->manageWorkspaceGroupRegistration(
                $parsedDatas['claro_register_group_to_workspace']
            );

            foreach ($logs as $log) {
                $output->writeln($log);
            }
            $output->writeln('');
        }
        $om->endFlushSuite();
    }
}
