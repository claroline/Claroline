<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\CoreBundle\Manager\Workspace\EvaluationManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EvaluationCommand extends ContainerAwareCommand implements AdminCliCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('claroline:evaluation:compute')
            ->setDescription('updates workspace & resource evaluations');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->processWorkspaces($output);
        $this->processResources($output);
    }

    private function processWorkspaces(OutputInterface $output)
    {
        $container = $this->getContainer();
        /** @var ObjectManager $om */
        $om = $container->get(ObjectManager::class);
        /** @var EvaluationManager $manager */
        $manager = $container->get('claroline.manager.workspace.evaluation');

        /** @var Evaluation[] $evaluations */
        $evaluations = $om->getRepository(Evaluation::class)->findAll();

        $output->writeln(sprintf('Computing workspace evaluations (status and duration)...'));

        $om->startFlushSuite();
        foreach ($evaluations as $i => $evaluation) {
            if ($evaluation->getWorkspace() && $evaluation->getUser()) {
                $manager->computeEvaluation($evaluation->getWorkspace(), $evaluation->getUser());
                $manager->computeDuration($evaluation);
            }

            if (0 === $i % 200) {
                $om->forceFlush();
            }
        }
        $om->endFlushSuite();

        $output->writeln('Done');
    }

    private function processResources(OutputInterface $output)
    {
        $container = $this->getContainer();
        /** @var ObjectManager $om */
        $om = $container->get(ObjectManager::class);
        /** @var ResourceEvaluationManager $manager */
        $manager = $container->get('claroline.manager.resource_evaluation_manager');

        /** @var ResourceUserEvaluation[] $evaluations */
        $evaluations = $om->getRepository(ResourceUserEvaluation::class)->findAll();

        $output->writeln(sprintf('Computing resource evaluations (duration)...'));

        $om->startFlushSuite();
        foreach ($evaluations as $i => $evaluation) {
            if ($evaluation->getResourceNode() && $evaluation->getUser()) {
                $manager->computeDuration($evaluation);
            }

            if (0 === $i % 200) {
                $om->forceFlush();
            }
        }
        $om->endFlushSuite();

        $output->writeln('Done');
    }
}
