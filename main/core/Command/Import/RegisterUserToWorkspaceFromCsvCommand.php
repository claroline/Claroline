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

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Command\BaseCommandTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterUserToWorkspaceFromCsvCommand extends Command
{
    use BaseCommandTrait;
    private $params = ['csv_workspace_registration_path' => 'Absolute path to the csv file: '];

    private $om;
    private $roleManager;
    private $finderProvider;

    public function __construct(ObjectManager $om, RoleManager $roleManager, FinderProvider $finderProvider)
    {
        $this->om = $om;
        $this->roleManager = $roleManager;
        $this->finderProvider = $finderProvider;

        parent::__construct();
    }

    protected function configure()
    {
        $this
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
        $this->addOption(
            'clean',
            'c',
            InputOption::VALUE_NONE,
            'When set to true, cleans the current permissions'
        );
        $this->addOption(
            'ignore',
            'i',
            InputOption::VALUE_OPTIONAL,
            'A workspace code string to ignore'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $roleRepo = $this->om->getRepository('ClarolineCoreBundle:Role');
        $userRepo = $this->om->getRepository('ClarolineCoreBundle:User');
        $groupRepo = $this->om->getRepository('ClarolineCoreBundle:Group');
        $workspaceRepo = $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace');

        $file = $input->getArgument('csv_workspace_registration_path');
        $lines = str_getcsv(file_get_contents($file), PHP_EOL);

        $clean = $input->getOption('clean');
        $ignore = $input->getOption('ignore');

        if ($clean) {
            foreach ($lines as $line) {
                $datas = str_getcsv($line, ';');
                $users[] = $datas[0];
            }

            $users = array_unique($users);

            $ignore = $this->finderProvider->fetch('Claroline\CoreBundle\Entity\Workspace\Workspace', ['code' => $ignore, 'isPersonal' => false]);

            $ignoreIds = array_map(function ($el) {
                return $el->getId();
            }, $ignore);

            $i = 1;

            $this->om->startFlushSuite();

            foreach ($users as $username) {
                //clean user roles except those in workspace matching $ignore
                $roles = $this->finderProvider->fetch('Claroline\CoreBundle\Entity\Role', ['user' => $username, 'type' => Role::WS_ROLE]);

                foreach ($roles as $role) {
                    if (!in_array($role->getWorkspace()->getId(), $ignoreIds)) {
                        $output->writeln(
                           "<info> Removing role {$role->getName()} from workspace {$role->getWorkspace()->getName()} from user {$username} </info>"
                        );
                        $user = $userRepo->findOneBy(['username' => $username]);

                        if ($user) {
                            $this->roleManager->dissociateRole($user, $role);
                            ++$i;
                        } else {
                            $output->writeln(
                                "<error> {$username} not found </error>"
                        );
                        }

                        if (0 === $i % 2000) {
                            $this->om->forceFlush();
                        }
                    }
                }
            }

            $this->om->endFlushSuite();
        }

        $this->om->clear();
        $i = 1;
        $this->om->startFlushSuite();

        foreach ($lines as $line) {
            $datas = str_getcsv($line, ';');

            if (4 === count($datas)) {
                $name = trim($datas[0]);
                $workspaceCode = trim($datas[1]);
                $roleKey = trim($datas[2]);
                $action = trim($datas[3]);
                $isGroup = 'register_group' === $action || 'unregister_group' === $action;
                $ars = $isGroup ? $groupRepo->findOneBy(['name' => $name]) : $userRepo->findOneBy(['username' => $name]);
                $workspace = $workspaceRepo->findOneBy(['code' => $workspaceCode]);

                if (!is_null($ars) && !is_null($workspace)) {
                    $roles = $roleRepo->findRolesByWorkspaceCodeAndTranslationKey(
                        $workspaceCode,
                        $roleKey
                    );

                    if (1 === count($roles)) {
                        if ('register' === $action) {
                            $this->roleManager->associateRole($ars, $roles[0], false);
                            $output->writeln(
                                "<info> Line $i: {User [$name] has been registered to workspace [$workspaceCode] with role [$roleKey].} </info>"
                            );
                        } elseif ('unregister' === $action) {
                            $this->roleManager->dissociateRole($ars, $roles[0]);
                            $output->writeln(
                                "<info> Line $i: {User [$name] has been unregistered from role [$roleKey] of workspace [$workspaceCode].} </info>"
                            );
                        } elseif ('register_group' === $action) {
                            $this->roleManager->associateRole($ars, $roles[0], false);
                            $output->writeln(
                                "<info> Line $i: {Group [$name] has been registered to workspace [$workspaceCode] with role [$roleKey].} </info>"
                            );
                        } elseif ('unregister_group' === $action) {
                            $this->roleManager->dissociateRole($ars, $roles[0]);
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

            if (0 === $i % 250) {
                $output->writeln('Flushing...');
                $this->om->forceFlush();
                $this->om->clear();
            }
            ++$i;
        }
        $this->om->endFlushSuite();
    }
}
