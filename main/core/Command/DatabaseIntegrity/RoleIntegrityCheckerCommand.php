<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RoleIntegrityCheckerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:roles:check')
            ->setDescription('Checks the role integrity of the platform.')
            ->addOption('user', 'u', InputOption::VALUE_OPTIONAL, 'User login or email. Restore roles only for this user.')
            ->addOption('user_index', 'i', InputOption::VALUE_OPTIONAL, 'Restore roles for users after given index.', 0)
            ->addOption('workspace', 'w', InputOption::VALUE_OPTIONAL, 'Workspace code. Restore roles only for this workspace.')
            ->addOption('workspace_index', 'j', InputOption::VALUE_OPTIONAL, 'Restore roles for workspaces after given index.', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = ConsoleLogger::get($output);
        $roleManager = $this->getContainer()->get('claroline.manager.role_manager');
        $roleManager->setLogger($consoleLogger);
        $userId = $input->getOption('user');
        $workspaceCode = $input->getOption('workspace');
        if (!empty($userId)) {
            $user = $this
                ->getContainer()
                ->get('claroline.manager.user_manager')
                ->getUserByUsernameOrMail($userId, $userId);
            if (empty($user)) {
                $consoleLogger->warning("Could not find user \"{$userId}\"");

                return;
            }
            $roleManager->checkUserIntegrity($user);

            return;
        } elseif (!empty($workspaceCode)) {
            $workspace = $this
                ->getContainer()
                ->get('claroline.manager.workspace_manager')
                ->getOneByCode($workspaceCode);
            if (empty($workspace)) {
                $consoleLogger->warning("Could not find workspace \"{$workspaceCode}\"");

                return;
            }
            $roleManager->checkWorkspaceIntegrity($workspace);

            return;
        }
        $userIdx = $input->getOption('user_index');
        $workspaceIdx = $input->getOption('workspace_index');
        $roleManager->checkIntegrity($workspaceIdx, $userIdx);
    }
}
