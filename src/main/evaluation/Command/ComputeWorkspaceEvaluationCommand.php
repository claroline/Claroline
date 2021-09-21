<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Command;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComputeWorkspaceEvaluationCommand extends Command
{
    private $om;
    private $evaluationManager;
    private $resourceEvaluationManager;

    public function __construct(
        ObjectManager $om,
        WorkspaceEvaluationManager $evaluationManager,
        ResourceEvaluationManager $resourceEvaluationManager
    ) {
        $this->om = $om;
        $this->evaluationManager = $evaluationManager;
        $this->resourceEvaluationManager = $resourceEvaluationManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Recomputes workspace evaluation based on resources to do and user progression.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->processWorkspaces($output);
        $this->processResources($output);

        return 0;
    }

    private function processWorkspaces(OutputInterface $output)
    {
        /** @var Evaluation[] $evaluations */
        $evaluations = $this->om->getRepository(Evaluation::class)->findAll();

        $output->writeln(sprintf('Computing workspace evaluations (status and duration)...'));

        $this->om->startFlushSuite();
        foreach ($evaluations as $i => $evaluation) {
            if ($evaluation->getWorkspace() && $evaluation->getUser()) {
                $this->evaluationManager->computeEvaluation($evaluation->getWorkspace(), $evaluation->getUser());
                $this->evaluationManager->computeDuration($evaluation);
            }

            if (0 === $i % 200) {
                $this->om->forceFlush();
            }
        }
        $this->om->endFlushSuite();

        $output->writeln('Done');
    }

    private function processResources(OutputInterface $output)
    {
        /** @var ResourceUserEvaluation[] $evaluations */
        $evaluations = $this->om->getRepository(ResourceUserEvaluation::class)->findAll();

        $output->writeln(sprintf('Computing resource evaluations (duration)...'));

        $this->om->startFlushSuite();
        foreach ($evaluations as $i => $evaluation) {
            if ($evaluation->getResourceNode() && $evaluation->getUser()) {
                $this->resourceEvaluationManager->computeDuration($evaluation);
            }

            if (0 === $i % 200) {
                $this->om->forceFlush();
            }
        }
        $this->om->endFlushSuite();

        $output->writeln('Done');
    }
}
