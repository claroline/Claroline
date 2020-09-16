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

use Claroline\AppBundle\Logger\ConsoleLogger;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RoleIntegrityCheckerCommand extends Command
{
    private $roleManager;
    private $userManager;
    private $om;

    public function __construct(RoleManager $roleManager, UserManager $userManager, ObjectManager $om)
    {
        $this->roleManager = $roleManager;
        $this->userManager = $userManager;
        $this->om = $om;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Checks the role integrity of the platform.')
            ->addOption('user', 'u', InputOption::VALUE_OPTIONAL, 'User login or email. Restore roles only for this user.')
            ->addOption('user_index', 'i', InputOption::VALUE_OPTIONAL, 'Restore roles for users after given index.', 0)
            ->addOption('workspace', 'w', InputOption::VALUE_OPTIONAL, 'Workspace code. Restore roles only for this workspace.')
            ->addOption('workspace_index', 'j', InputOption::VALUE_OPTIONAL, 'Restore roles for workspaces after given index.', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consoleLogger = ConsoleLogger::get($output);
        $this->roleManager->setLogger($consoleLogger);
        $userId = $input->getOption('user');
        $workspaceCode = $input->getOption('workspace');

        if (!empty($userId)) {
            $user = $this->userManager->getUserByUsernameOrMail($userId, $userId);
            if (empty($user)) {
                $consoleLogger->warning("Could not find user \"{$userId}\"");

                return 1;
            }
            $this->roleManager->checkUserIntegrity($user);

            return 0;
        } elseif (!empty($workspaceCode)) {
            $workspace = $this->om->getRepository(Workspace::class)->findOneByCode($workspaceCode);
            if (empty($workspace)) {
                $consoleLogger->warning("Could not find workspace \"{$workspaceCode}\"");

                return 1;
            }
            $this->roleManager->checkWorkspaceIntegrity($workspace);

            return 0;
        }

        $userIdx = $input->getOption('user_index');
        $workspaceIdx = $input->getOption('workspace_index');
        $this->roleManager->checkIntegrity($workspaceIdx, $userIdx);

        return 0;
    }
}
