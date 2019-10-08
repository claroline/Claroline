<?php

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RootNodeIntegrityCheckerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:resource_root:check')
            ->setDescription('Checks the workspace roots integrity');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $om = $this->getContainer()->get('Claroline\AppBundle\Persistence\ObjectManager');
        $roots = $om->getRepository(ResourceNode::class)->findBy(['parent' => null, 'personal' => false]);

        $i = 0;

        foreach ($roots as $root) {
            if ($root->getWorkspace()) {
                $root->setName($root->getWorkspace()->getName());

                $output->writeln("Set root name for node {$root->getName()} to {$root->getWorkspace()->getName()}");
            }
            $om->persist($root);
            ++$i;

            if (0 === $i % 100) {
                $output->writeln('flush...');
                $om->flush();
            }
        }
        $output->writeln('flush...');
        $om->flush();
    }
}
