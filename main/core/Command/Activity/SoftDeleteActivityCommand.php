<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Activity;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Soft deletes Activity resources.
 */
class SoftDeleteActivityCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:activity:soft_delete')
            ->setDescription('Soft deletes Activity resources');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $activityRepo = $om->getRepository('Claroline\CoreBundle\Entity\Resource\Activity');
        $allActivities = $activityRepo->findAll();

        $om->startFlushSuite();
        $i = 1;

        foreach ($allActivities as $dropzone) {
            $node = $dropzone->getResourceNode();
            $output->writeln('<info>  Soft deleting resource ['.$node->getName().']...</info>');
            $node->setActive(false);
            $om->persist($node);

            $output->writeln('<info>  Resource soft deleted.</info>');

            if ($i % 100 === 0) {
                $output->writeln('<info>  Flushing...</info>');
                $om->forceFlush();
            }
        }
        $om->endFlushSuite();
    }
}
