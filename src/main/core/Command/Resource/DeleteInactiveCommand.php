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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteInactiveCommand extends Command
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly Crud $crud
    ) {
        parent::__construct();
    }

    protected function configure(): void
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
            $filters['workspace'] = $this->om->getRepository(Workspace::class)->findOneBy([
                'uuid' => $input->getOption('workspace'),
            ]);
        }

        $resources = $this->om->getRepository(ResourceNode::class)->findBy($filters, [], $input->getOption('limit'));

        $this->om->startFlushSuite();

        $output->writeln(sprintf('Found %d inactive resources / Will delete %d resources.', count($resources), $input->getOption('limit')));
        foreach ($resources as $resource) {
            $output->writeln(sprintf('Deleting "%s" (%s).', $resource->getName(), $resource->getUuid()));

            if ($input->getOption('force')) {
                $this->crud->delete($resource, [Crud::NO_PERMISSIONS]);
            }
        }

        $this->om->endFlushSuite();

        return 0;
    }
}
