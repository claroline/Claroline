<?php

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RootNodeIntegrityCheckerCommand extends Command
{
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('claroline:resource_root:check')
            ->setDescription('Checks the workspace roots integrity');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $roots = $this->om->getRepository(ResourceNode::class)->findBy(['parent' => null, 'personal' => false]);

        $i = 0;

        foreach ($roots as $root) {
            if ($root->getWorkspace()) {
                $root->setName($root->getWorkspace()->getName());

                $output->writeln("Set root name for node {$root->getName()} to {$root->getWorkspace()->getName()}");
            }
            $this->om->persist($root);
            ++$i;

            if (0 === $i % 100) {
                $output->writeln('flush...');
                $this->om->flush();
            }
        }
        $output->writeln('flush...');
        $this->om->flush();

        return 0;
    }
}
