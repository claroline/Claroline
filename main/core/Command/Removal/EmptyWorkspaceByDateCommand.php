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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Removes users from a workspace.
 */
class EmptyWorkspaceByDateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspace:empty-by-date')
            ->setDescription('Empty workspaces');
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
        $workspaces = $this->getContainer()->get(WorkspaceFinder::class)->find(['createdBefore' => $beforeDate]);
        $output->writeln('Found '.count($workspaces).' workspaces');
        $roleManager = $this->getContainer()->get(RoleManager::class);

        foreach ($workspaces as $workspace) {
            if (!in_array($workspace->getCode(), $codes)) {
                foreach ($workspace->getRoles() as $role) {
                    if ($role !== $workspace->getManagerRole()) {
                        $output->writeln("Cleaning role {$role->getTranslationKey()} from {$workspace->getCode()}");
                        $roleManager->emptyRole($role, RoleManager::EMPTY_GROUPS);
                        $roleManager->emptyRole($role, RoleManager::EMPTY_USERS);
                    }
                }
            }
        }
    }
}
