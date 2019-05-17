<?php

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResourceSlugBuilderCommand extends ContainerAwareCommand
{
    const BATCH_SIZE = 500;

    protected function configure()
    {
        $this->setName('claroline:resource:slug')
            ->setDescription('Rebuild the resources slug');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $total = $om->count(ResourceNode::class);
        $output->writeln('Building resource slug ('.$total.')');

        $offset = 0;

        while ($offset < $total) {
            $nodes = $om->getRepository(ResourceNode::class)->findBy([], [], self::BATCH_SIZE, $offset);

            foreach ($nodes as $node) {
                //rebuild slug
                $node->setSlug(null);
                $om->persist($node);
                $output->writeln('Building slug for resource '.$node->getPathForDisplay());
                ++$offset;
            }

            $output->writeln('Flush');
            $om->flush();
            $om->clear();
        }
    }
}
