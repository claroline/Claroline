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

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteArchivedCommand extends Command
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
            ->setDescription('Delete all archived workspaces')
            ->setDefinition([
                new InputOption('force', 'f', InputOption::VALUE_NONE, 'The deletion will only be executed if this option is present.'),
                new InputOption('limit', 'l', InputOption::VALUE_REQUIRED, 'The number of workspaces which will be deleted.', 50),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $workspaces = $this->finder->searchEntities(Workspace::class, [
            'filters' => [
                'archived' => true,
            ],
            'limit' => $input->getOption('limit'),
        ]);

        $this->om->startFlushSuite();

        $output->writeln(sprintf('Found %d archived workspaces / Will delete %d workspaces.', $workspaces['totalResults'], $input->getOption('limit')));
        foreach ($workspaces['data'] as $workspace) {
            $output->writeln(sprintf('Deleting "%s" (%s).', $workspace->getName(), $workspace->getUuid()));

            if ($input->getOption('force')) {
                $this->crud->delete($workspace, [Crud::NO_PERMISSIONS]);
            }
        }

        $this->om->endFlushSuite();

        return 0;
    }
}
