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

class RegisterUserToWorkspaceFromCsvCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspace:register')
            ->setDescription('Registers users to workspaces from a csv file');
        $this->setDefinition(
            array(
                new InputArgument(
                    'csv_workspace_registration_path',
                    InputArgument::REQUIRED,
                    'The absolute path to the csv file.'
                )
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'csv_workspace_registration_path' => 'Absolute path to the csv file: '
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $roleManager = $this->getContainer()->get('claroline.manager.role_manager');
        $roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $workspaceRepo = $om->getRepository('ClarolineCoreBundle:Workspace\Workspace');

        $file = $input->getArgument('csv_workspace_registration_path');
        $lines = str_getcsv(file_get_contents($file), PHP_EOL);
        
        $om->startFlushSuite();

        $i = 1;

        foreach ($lines as $line) {
            $datas = str_getcsv($line, ';');

            if (count($datas) === 4) {
                $username = trim($datas[0]);
                $workspaceCode = trim($datas[1]);
                $roleKey = trim($datas[2]);
                $action = trim($datas[3]);

                $user = $userRepo->findOneBy(array('username' => $username));
                $workspace = $workspaceRepo->findOneBy(array('code' => $workspaceCode));

                if (!is_null($user) && !is_null($workspace)) {
                    $roles = $roleRepo->findRolesByWorkspaceCodeAndTranslationKey(
                        $workspaceCode,
                        $roleKey
                    );

                    if (count($roles) === 1) {

                        if ($action === 'register') {
                            $roleManager->associateRole($user, $roles[0]);
                            $output->writeln(
                                "<info> Line $i: {User [$username] has been registered to workspace [$workspaceCode] with role [$roleKey].} </info>"
                            );
                        } elseif ($action === 'unregister') {
                            $roleManager->dissociateRole($user, $roles[0]);
                            $output->writeln(
                                "<info> Line $i: {User [$username] has been unregistered from role [$roleKey] of workspace [$workspaceCode].} </info>"
                            );
                        }
                        else {
                            $output->writeln(
                                "<error> Line $i: {Unknown action [$action]. Allowed actions are [register] and [unregister]} </error>"
                            );
                        }
                    } elseif (count($roles) < 1) {
                        $output->writeln(
                            "<error> Line $i: {No role has been found for translation key [$roleKey].} </error>"
                        );
                    } else {
                        $output->writeln(
                            "<error> Line $i: {Several roles have been found for translation key [$roleKey].} </error>"
                        );
                    }
                } else {

                    if (is_null($user)){
                        $output->writeln("<error> Line $i: {User [$username] doesn't exist.} </error>");
                    }

                    if (is_null($workspace)){
                        $output->writeln("<error> Line $i: {Workspace [$workspaceCode] doesn't exist.} </error>");
                    }
                }
            } else {
                $output->writeln("<error> Line $i: {Each row must have 4 parameters. Required format is [Username];[Workspace code];[Role translation key];[register|unregister]} </error>");
            }

            if ($i % 100 === 0) {
                $om->forceFlush();
                $om->clear();
            }
            $i++;
        }
        $om->endFlushSuite();
    }
}
