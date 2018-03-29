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
 * Definitively deletes soft-deleted Activity resources.
 */
class DeleteActivityCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:activity:delete')
            ->setDescription('Definitvely deletes soft-deleted Activity resources');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $resourceManager = $this->getContainer()->get('claroline.manager.resource_manager');
        $activityRepo = $om->getRepository('Claroline\CoreBundle\Entity\Resource\Activity');
        $allActivities = $activityRepo->findAll();
        $startDate = new \DateTime();
        $nbResources = 0;

        $om->startFlushSuite();
        $i = 1;

        foreach ($allActivities as $dropzone) {
            $node = $dropzone->getResourceNode();

            if (!$node->isActive()) {
                $output->writeln('<info>  Deleting resource ['.$node->getName().']...</info>');

                $resourceManager->delete($node);

                $output->writeln('<info>  Resource deleted.</info>');
                ++$nbResources;

                if ($i % 100 === 0) {
                    $output->writeln('<info>  Flushing...</info>');
                    $om->forceFlush();
                }
                ++$i;
            }
        }
        $om->endFlushSuite();

        $timeDiff = $startDate->diff(new \DateTime());
        $hours = ($timeDiff->days * 24) + $timeDiff->h;
        $minutes = $timeDiff->i;
        $seconds = $timeDiff->s;
        $output->writeln("<info>  Execution time : $hours hours $minutes minutes $seconds seconds</info>");
        $output->writeln("<info>  Number of resources : $nbResources</info>");
    }
}
