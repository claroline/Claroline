<?php

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\ResourceManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DirectoryIntegrityCommand extends Command
{
    private $om;
    private $resourceManager;

    public function __construct(ObjectManager $om, ResourceManager $resourceManager)
    {
        $this->om = $om;
        $this->resourceManager = $resourceManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Checks the directory integrity of a workspace.')
            ->addOption('workspace', 'w', InputOption::VALUE_OPTIONAL, 'The workspace code');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $code = $input->getOption('workspace');

        if ($code) {
            $workspace = $this->om->getRepository(Workspace::class)->findOneByCode($code);
            $dirType = $this->om->getRepository(ResourceType::class)->findOneByName('directory');

            $nodes = $this->om->getRepository(ResourceNode::class)->findBy([
                'workspace' => $workspace,
                'resourceType' => $dirType,
            ]);

            foreach ($nodes as $node) {
                $directory = $this->resourceManager->getResourceFromNode($node);

                if (!$directory) {
                    $newDir = new Directory();
                    $newDir->setResourceNode($node);

                    $this->om->persist($newDir);
                    $output->writeln("Add directory for node {$node->getName()}");
                }
            }

            $output->writeln('Flushing...');
            $this->om->flush();
        }

        return 0;
    }
}
