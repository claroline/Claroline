<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MaterializedPathBuilderCommand extends ContainerAwareCommand
{
    const BATCH_SIZE = 500;

    protected function configure()
    {
        $this->setName('claroline:resources:build-path')
            ->setDescription('Rebuild resource materializedPath... might be very long');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $om = $this->getContainer()->get('Claroline\AppBundle\Persistence\ObjectManager');
        $total = $om->count(ResourceNode::class);
        $output->writeln('Building resource paths ('.$total.')');

        $offset = 0;

        while ($offset < $total) {
            $nodes = $om->getRepository(ResourceNode::class)->findBy([], [], self::BATCH_SIZE, $offset);

            foreach ($nodes as $node) {
                $skip = false;
                $output->writeln($node->getName().' - '.$node->getId());
                $ancestors = $node->getOldAncestors();
                $ids = array_map(function ($ancestor) {
                    return $ancestor['id'];
                }, $ancestors);
                $ids = array_unique($ids);

                if (count($ids) !== count($ancestors)) {
                    $skip = true;
                    $om->detach($node);
                }

                if (!$skip) {
                    $om->persist($node);
                } else {
                    $output->writeln('unset '.$node->getName().' - '.$node->getId());
                    unset($node);
                }
                ++$offset;
                $output->writeln('Building resource paths '.$offset.'/'.$total);
            }

            $output->writeln('Flush');
            $om->flush();
            $om->clear();
        }
    }
}
