<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteInactiveCommand extends Command
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
            ->setDescription('Delete all inactive resources')
            ->setDefinition([
                new InputOption('force', 'f', InputOption::VALUE_NONE, 'The deletion will only be executed if this option is present.'),
                new InputOption('workspace', 'w', InputOption::VALUE_OPTIONAL, 'The UUID of the workspace to filter on.'),
                new InputOption('limit', 'l', InputOption::VALUE_REQUIRED, 'The number of resources which will be deleted.', -1),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filters = [
            'active' => false,
        ];
        if (!empty($input->getOption('workspace'))) {
            $filters['workspace'] = $input->getOption('workspace');
        }

        $resources = $this->finder->searchEntities(ResourceNode::class, [
            'filters' => $filters,
            'limit' => $input->getOption('limit'),
        ]);

        $this->om->startFlushSuite();

        $output->writeln(sprintf('Found %d inactive resources / Will delete %d resources.', $resources['totalResults'], $input->getOption('limit')));
        foreach ($resources['data'] as $resource) {
            $output->writeln(sprintf('Deleting "%s" (%s).', $resource->getName(), $resource->getUuid()));

            if ($input->getOption('force')) {
                $this->crud->delete($resource, [Crud::NO_PERMISSIONS]);
            }
        }

        $this->om->endFlushSuite();

        return 0;
    }
}
