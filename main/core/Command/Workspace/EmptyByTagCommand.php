<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Workspace;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\TagBundle\Entity\TaggedObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Removes users from a workspace.
 */
class EmptyByTagCommand extends Command
{
    private $om;
    private $roleManager;

    public function __construct(ObjectManager $om, RoleManager $roleManager)
    {
        $this->om = $om;
        $this->roleManager = $roleManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('claroline:workspace:empty')
            ->setDescription('Empty workspaces')
            ->setDefinition([
                new InputArgument('role_key', InputArgument::REQUIRED, 'The role translation key'),
                new InputArgument('except_tag', InputArgument::OPTIONAL, 'Do not empty workspace with this tag'),
            ])
            ->addOption('user', 'u', InputOption::VALUE_NONE, 'When set to true, remove users from the workspace')
            ->addOption('group', 'g', InputOption::VALUE_NONE, 'When set to true, remove groups from the workspace');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $removeUsers = $input->getOption('user');
        $removeGroups = $input->getOption('group');

        $tag = $input->getArgument('except_tag');
        // the role to remove
        $roleKey = $input->getArgument('role_key');

        $workspaces = $this->om->getRepository(Workspace::class)->findAll();

        $output->writeln(sprintf('Found %d workspaces to empty', count($workspaces)));
        $this->om->startFlushSuite();
        foreach ($workspaces as $i => $workspace) {
            $output->writeln(sprintf('Processing Workspace %s (%s)', $workspace->getName(), $workspace->getUuid()));

            if ($tag) {
                $taggedObj = $this->om->getRepository(TaggedObject::class)->findOneTaggedObjectByTagNameAndObject($tag, $workspace->getUuid(), Workspace::class);
                if ($taggedObj) {
                    $output->writeln(sprintf('Workspace has tag %s. Skip workspace.', $tag));
                    continue;
                }
            }

            $role = $this->om->getRepository(Role::class)->findOneBy([
                'workspace' => $workspace,
                'translationKey' => $roleKey,
            ]);

            if (empty($role)) {
                $output->writeln(sprintf('Role %s cannot be found. Skip workspace.', $roleKey));
            } else {
                if ($removeUsers) {
                    $count = $this->om->getRepository('ClarolineCoreBundle:User')->countUsersByRole($role);
                    $output->writeln("Removing {$count} users from role {$role->getTranslationKey()}");
                    $this->roleManager->emptyRole($role, RoleManager::EMPTY_USERS);
                }

                if ($removeGroups) {
                    $count = $this->om->getRepository('ClarolineCoreBundle:Group')->countGroupsByRole($role);
                    $output->writeln("Removing {$count} groups from role {$role->getTranslationKey()}");
                    $this->roleManager->emptyRole($role, RoleManager::EMPTY_GROUPS);
                }
            }

            if (0 === $i % 100) {
                $this->om->forceFlush();
            }
        }
        $this->om->endFlushSuite();
    }
}
