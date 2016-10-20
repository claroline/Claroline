<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Removal;

use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Removes users from a workspace.
 */
class EmptyWorkspaceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspace:empty')
            ->setDescription('Empty workspaces');

        $this->addOption(
            'user',
            'u',
            InputOption::VALUE_NONE,
            'When set to true, remove users from the workspace'
        );

        $this->addOption(
            'group',
            'g',
            InputOption::VALUE_NONE,
            'When set to true, remove groups from the workspace'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $removeUsers = $input->getOption('user');
        $removeGroups = $input->getOption('group');

        $container = $this->getContainer();
        $helper = $this->getHelper('question');
        $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');
        $roleManager = $this->getContainer()->get('claroline.manager.role_manager');
        $question = new Question('Filter on code (continue if no filter)', null);
        $code = $helper->ask($input, $output, $question);
        $question = new Question('Filter on name (continue if no filter)', null);
        $name = $helper->ask($input, $output, $question);
        $workspaces = $workspaceManager->getNonPersonalByCodeAndName($code, $name);
        $om = $container->get('claroline.persistence.object_manager');

        foreach ($workspaces as $workspace) {
            $roles = $roleManager->getRolesByWorkspace($workspace);

            $roleNames = array_map(function ($role) {
                return $role->getTranslationKey();
            }, $roles);
            $roleNames[] = 'NONE';

            $questionString = "Pick a role list for [{$workspace->getName()} - {$workspace->getCode()}]:";
            $question = new ChoiceQuestion($questionString, $roleNames);
            $question->setMultiselect(true);
            $roleNames = $helper->ask($input, $output, $question);

            $pickedRoles = array_filter($roles, function ($role) use ($roleNames) {
                return in_array($role->getTranslationKey(), $roleNames);
            });

            $om->startFlushSuite();

            foreach ($pickedRoles as $role) {
                if ($removeUsers) {
                    $count = $om->getRepository('ClarolineCoreBundle:User')->countUsersByRole($role);
                    $output->writeln("Removing {$count} users from role {$role->getTranslationKey()}");
                    $roleManager->emptyRole($role, RoleManager::EMPTY_USERS);
                }

                if ($removeGroups) {
                    $count = $om->getRepository('ClarolineCoreBundle:Group')->countGroupsByRole($role);
                    $output->writeln("Removing {$count} groups from role {$role->getTranslationKey()}");
                    $roleManager->emptyRole($role, RoleManager::EMPTY_GROUPS);
                }
            }

            $om->endFlushSuite();
        }
    }
}
