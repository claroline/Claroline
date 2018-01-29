<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Tracking;

use Claroline\CoreBundle\Event\GenericDataEvent;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateResourceTrackingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('claroline:tracking:generate')
            ->setDescription('Generate tracking for resources from logs');

        $this->addOption(
            'days',
            'd',
            InputOption::VALUE_OPTIONAL,
            'When set, only logs from past d days are used to compute tracking'
        );
        $this->addOption(
            'types',
            't',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'When set, only computes tracking for the listed resource types (name)'
        );
        $this->addOption(
            'users',
            'u',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'When set, only computes tracking for the listed users (username)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Generating resource trackings...</comment>');
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $resourceTypeRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $nodeRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceNode');

        /* Fetch options */
        $days = intval($input->getOption('days'));
        $types = $input->getOption('types');
        $usernames = $input->getOption('users');

        $users = count($usernames) > 0 ?
            $userRepo->findEnabledUsersByUsernames($usernames) :
            $userRepo->findBy(['isEnabled' => true, 'isRemoved' => false]);
        $resourceTypes = count($types) > 0 ?
            $resourceTypeRepo->findEnabledResourceTypesByNames($types) :
            $resourceTypeRepo->findBy(['isEnabled' => true]);
        $startDate = null;

        if ($days) {
            $startDate = new \DateTime();
            $startDate->modify("-$days day");
            $startDate->setTime(0, 0);
        }

        $om->startFlushSuite();

        $output->writeln('--------------------');
        $i = 0;

        foreach ($users as $user) {
            $output->writeln('<info>User "'.$user->getFirstName().' '.$user->getLastName().'" : starting...</info>');

            foreach ($resourceTypes as $resourceType) {
                $output->writeln('<info>    Resource type "'.$resourceType->getName().'" : starting...</info>');
                $nodes = $nodeRepo->findBy(['resourceType' => $resourceType, 'published' => true, 'active' => true]);

                $output->writeln('        --------------------');

                foreach ($nodes as $node) {
                    $output->writeln('<info>        Resource "'.$node->getName().'" : starting...</info>');
                    $dispatcher->dispatch(
                        'generate_resource_user_evaluation_'.$resourceType->getName(),
                        new GenericDataEvent([
                            'resourceNode' => $node,
                            'user' => $user,
                            'startDate' => $startDate,
                        ])
                    );
                    $output->writeln('<info>        Resource "'.$node->getName().'" : finished.</info>');
                    $output->writeln('        --------------------');
                    ++$i;

                    if ($i % 200 === 0) {
                        $om->forceFlush();
                        $output->writeln('Processed');
                    }
                }
                $output->writeln('<info>    Resource type "'.$resourceType->getName().'" : finished.</info>');
            }
            $output->writeln('<info>User "'.$user->getFirstName().' '.$user->getLastName().'" : finished.</info>');
            $output->writeln('--------------------');
        }

        $om->endFlushSuite();

        $output->writeln('<comment>Generation of resource trackings is finished.</comment>');
    }
}
