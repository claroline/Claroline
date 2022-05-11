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
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteDisabledCommand extends Command
{
    /** @var ObjectManager */
    private $om;
    /** @var FinderProvider */
    private $finder;
    /** @var Crud */
    private $crud;

    public function __construct(ObjectManager $om, FinderProvider $finder, Crud $crud)
    {
        $this->om = $om;
        $this->finder = $finder;
        $this->crud = $crud;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Delete all disabled users')
            ->setDefinition([
                new InputOption('soft', 's', InputOption::VALUE_NONE, 'Do a soft delete.'),
                new InputOption('force', 'f', InputOption::VALUE_NONE, 'The deletion will only be executed if this option is present.'),
                new InputOption('limit', 'l', InputOption::VALUE_REQUIRED, 'The number of users which will be deleted.', 50),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->finder->searchEntities(User::class, [
            'filters' => [
                'isEnabled' => false,
            ],
            'limit' => $input->getOption('limit'),
        ]);

        $this->om->startFlushSuite();

        $output->writeln(sprintf('Found %d disabled users / Will delete %d users.', $users['totalResults'], $input->getOption('limit')));
        foreach ($users['data'] as $user) {
            $output->writeln(sprintf('Deleting "%s" (%s).', $user->getUsername(), $user->getUuid()));

            if ($input->getOption('force')) {
                $options = [Crud::NO_PERMISSIONS];
                if ($input->getOption('soft')) {
                    $options[] = Options::SOFT_DELETE;
                }

                $this->crud->delete($user, $options);
            }
        }

        $this->om->endFlushSuite();

        return 0;
    }
}
