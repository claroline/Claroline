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
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComputeWorkspaceEvaluationCommand extends Command
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly WorkspaceEvaluationManager $evaluationManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Recomputes workspace evaluation based on resources to do and user progression.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Evaluation[] $evaluations */
        $evaluations = $this->om->getRepository(Evaluation::class)->findAll();

        $output->writeln('Computing workspace evaluations...');

        $this->om->startFlushSuite();
        foreach ($evaluations as $i => $evaluation) {
            if ($evaluation->getWorkspace() && $evaluation->getUser()) {
                $this->evaluationManager->computeEvaluation($evaluation->getWorkspace(), $evaluation->getUser());
            }

            if (0 === $i % 200) {
                $this->om->forceFlush();
            }
        }
        $this->om->endFlushSuite();

        $output->writeln('Done');

        return 0;
    }
}
