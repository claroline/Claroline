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

use Claroline\CoreBundle\API\Finder\Workspace\WorkspaceFinder;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Removes users from a workspace.
 */
class EmptyWorkspaceByDateCommand extends Command
{
    private $workspaceFinder;
    private $roleManager;

    public function __construct(WorkspaceFinder $workspaceFinder, RoleManager $roleManager)
    {
        $this->workspaceFinder = $workspaceFinder;
        $this->roleManager = $roleManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Empty workspaces');
        $this->setDefinition(
            [
                new InputArgument('before', InputArgument::REQUIRED, 'Before date d/m/Y'),
                new InputArgument('codes', InputArgument::OPTIONAL, 'The codes'),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $codes = $input->getArgument('codes');
        $before = $input->getArgument('before');

        $codes = explode(',', $codes);
        $codes = array_filter($codes, function ($code) {
            return trim($code);
        });

        $beforeDate = \DateTime::createFromFormat('d/m/Y', $before);
        $workspaces = $this->workspaceFinder->find(['createdBefore' => $beforeDate]);
        $output->writeln('Found '.count($workspaces).' workspaces');

        foreach ($workspaces as $workspace) {
            if (!in_array($workspace->getCode(), $codes)) {
                foreach ($workspace->getRoles() as $role) {
                    if ($role !== $workspace->getManagerRole()) {
                        $output->writeln("Cleaning role {$role->getTranslationKey()} from {$workspace->getCode()}");
                        $this->roleManager->emptyRole($role, RoleManager::EMPTY_GROUPS);
                        $this->roleManager->emptyRole($role, RoleManager::EMPTY_USERS);
                    }
                }
            }
        }
    }
}
