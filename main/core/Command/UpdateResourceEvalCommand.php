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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateResourceEvalCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:evaluation:update_progression')
            ->setDescription('Updates progression of all resource evaluations');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>  Updating progression of resource user evaluations...</info>');
        $nbUpdated = $this->updateResourceUserEvaluationProgression();
        $output->writeln("<info>  Progression of resource user evaluations updated. ($nbUpdated)</info>");
    }

    private function updateResourceUserEvaluationProgression()
    {
        /** @var ObjectManager $om */
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $dql = '
            SELECT e
            FROM Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation e
            JOIN e.resourceNode r
            JOIN r.resourceType rt
            WHERE e.progressionMax IS NULL
            AND (
                e.progression IS NOT NULL
                OR rt.name = :pathType
            )
        ';
        $query = $om->createQuery($dql);
        $query->setParameter('pathType', 'innova_path');
        $resUserEvals = $query->getResult();
        $i = 0;
        $nbUpdated = 0;

        $om->startFlushSuite();

        foreach ($resUserEvals as $eval) {
            if ('innova_path' === $eval->getResourceNode()->getResourceType()->getName()) {
                if (!is_null($eval->getScore()) && !is_null($eval->getScoreMax())) {
                    $eval->setProgression($eval->getScore());
                    $eval->setProgressionMax($eval->getScoreMax());
                    $eval->setScore(null);
                    $eval->setScoreMax(null);
                    $om->persist($eval);
                    ++$nbUpdated;
                } elseif (!is_null($eval->getProgression())) {
                    $eval->setProgressionMax(100);
                    $om->persist($eval);
                    ++$nbUpdated;
                }
            } else {
                $eval->setProgressionMax(100);
                $om->persist($eval);
                ++$nbUpdated;
            }
            ++$i;

            if (0 === $i % 200) {
                $om->forceFlush();
            }
        }
        $om->endFlushSuite();

        return $nbUpdated;
    }
}
