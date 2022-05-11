<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Command;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddGroupCommand extends Command
{
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var FinderProvider */
    private $finder;

    public function __construct(ObjectManager $om, Crud $crud, FinderProvider $finder)
    {
        $this->om = $om;
        $this->crud = $crud;
        $this->finder = $finder;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add users to a group based on their email address.')
            ->addArgument('email', InputArgument::REQUIRED, 'The string to search in email address')
            ->addArgument('group', InputArgument::REQUIRED, 'The name of the group');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->om = $this->getContainer()->get(ObjectManager::class);
        $this->finder = $this->getContainer()->get(FinderProvider::class);
        $this->crud = $this->getContainer()->get(Crud::class);

        $group = $this->om->getRepository(Group::class)->findOneBy(['name' => $input->getArgument('group')]);
        if (!$group) {
            throw new \Exception('Group cannot be found.');
        }

        $users = $this->finder->searchEntities(User::class, [
            'filters' => ['email' => $input->getArgument('email')],
        ]);

        $output->writeln(sprintf('Found %d users to add to group %s.', $users['totalResults'], $group->getName()));

        $this->om->startFlushSuite();

        foreach ($users['data'] as $i => $user) {
            $output->writeln(sprintf('Add user %s to group.', $user->getEmail()));

            $this->crud->patch($user, 'group', Crud::COLLECTION_ADD, [$group]);

            if (0 === $i % 200) {
                $this->om->forceFlush();
            }
        }

        $this->om->flush();
        $this->om->endFlushSuite();

        return 0;
    }
}
