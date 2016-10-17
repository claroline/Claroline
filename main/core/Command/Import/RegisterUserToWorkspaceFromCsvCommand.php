<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Import;

use Claroline\CoreBundle\Command\Traits\BaseCommandTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterUserToWorkspaceFromCsvCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;
    private $params = ['csv_workspace_registration_path' => 'Absolute path to the csv file: '];

    protected function configure()
    {
        $this->setName('claroline:workspace:register')
            ->setDescription('Registers users to workspaces from a csv file')
            ->setAliases(['claroline:csv:workspace_register']);
        $this->setDefinition(
            [
                new InputArgument(
                    'csv_workspace_registration_path',
                    InputArgument::REQUIRED,
                    'The absolute path to the csv file.'
                ),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $roleManager = $this->getContainer()->get('claroline.manager.role_manager');
        $roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $workspaceRepo = $om->getRepository('ClarolineCoreBundle:Workspace\Workspace');

        $file = $input->getArgument('csv_workspace_registration_path');
        $lines = str_getcsv(file_get_contents($file), PHP_EOL);

        $om->startFlushSuite();

        $i = 1;

        foreach ($lines as $line) {
            $datas = str_getcsv($line, ';');

            if (count($datas) === 4) {
                $name = trim($datas[0]);
                $workspaceCode = trim($datas[1]);
                $roleKey = trim($datas[2]);
                $action = trim($datas[3]);
                $isGroup = $action === 'register_group' || $action === 'unregister_group';
                $ars = $isGroup ? $groupRepo->findOneBy(['name' => $name]) : $userRepo->findOneBy(['username' => $name]);
                $workspace = $workspaceRepo->findOneBy(['code' => $workspaceCode]);

                if (!is_null($ars) && !is_null($workspace)) {
                    $roles = $roleRepo->findRolesByWorkspaceCodeAndTranslationKey(
                        $workspaceCode,
                        $roleKey
                    );

                    if (count($roles) === 1) {
                        if ($action === 'register') {
                            $roleManager->associateRole($ars, $roles[0]);
                            $output->writeln(
                                "<info> Line $i: {User [$name] has been registered to workspace [$workspaceCode] with role [$roleKey].} </info>"
                            );
                        } elseif ($action === 'unregister') {
                            $roleManager->dissociateRole($ars, $roles[0]);
                            $output->writeln(
                                "<info> Line $i: {User [$name] has been unregistered from role [$roleKey] of workspace [$workspaceCode].} </info>"
                            );
                        } elseif ($action === 'register_group') {
                            $roleManager->associateRole($ars, $roles[0]);
                            $output->writeln(
                                "<info> Line $i: {Group [$name] has been registered to workspace [$workspaceCode] with role [$roleKey].} </info>"
                            );
                        } elseif ($action === 'unregister_group') {
                            $roleManager->dissociateRole($ars, $roles[0]);
                            $output->writeln(
                                "<info> Line $i: {Group [$name] has been unregistered from role [$roleKey] of workspace [$workspaceCode].} </info>"
                            );
                        } else {
                            $output->writeln(
                                "<error> Line $i: {Unknown action [$action]. Allowed actions are [register], [unregister], [register_group] and [unregister_group]} </error>"
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
                    if (is_null($ars)) {
                        if ($isGroup) {
                            $output->writeln("<error> Line $i: {Group [$name] doesn't exist.} </error>");
                        } else {
                            $output->writeln("<error> Line $i: {User [$name] doesn't exist.} </error>");
                        }
                    }

                    if (is_null($workspace)) {
                        $output->writeln("<error> Line $i: {Workspace [$workspaceCode] doesn't exist.} </error>");
                    }
                }
            } else {
                $output->writeln("<error> Line $i: {Each row must have 4 parameters. Required format is [Username|Group name];[Workspace code];[Role translation key];[register|unregister|register_group|unregister_group]} </error>");
            }

            if ($i % 100 === 0) {
                $om->forceFlush();
                $om->clear();
            }
            ++$i;
        }
        $om->endFlushSuite();
    }
}
