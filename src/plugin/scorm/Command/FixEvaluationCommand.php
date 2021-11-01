<?php

namespace Claroline\ScormBundle\Command;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\ScormBundle\Entity\ScoTracking;
use Claroline\ScormBundle\Manager\ScormManager;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixEvaluationCommand extends Command
{
    /** @var Connection */
    private $connection;
    /** @var ObjectManager */
    private $om;
    /** @var ScormManager */
    private $manager;
    /** @var ResourceEvaluationManager */
    private $evaluationManager;

    public function __construct(
        Connection $connection,
        ObjectManager $om,
        ScormManager $manager,
        ResourceEvaluationManager $evaluationManager
    ) {
        $this->connection = $connection;
        $this->om = $om;
        $this->manager = $manager;
        $this->evaluationManager = $evaluationManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Recompute ResourceUserEvaluation for scorms based on ScoTracking.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->generateMissingAttempts($output);
        $this->syncStatuses($output);

        return 0;
    }

    private function generateMissingAttempts(OutputInterface $output)
    {
        // retrieve ScoTracking with missing ResourceEvaluation
        $statement = $this->connection->query('
            SELECT t.id
            FROM claro_scorm_sco_tracking AS t
            LEFT JOIN claro_scorm_sco AS sco ON (t.sco_id = sco.id)
            LEFT JOIN claro_scorm AS s ON (sco.scorm_id = s.id)
            LEFT JOIN claro_resource_node AS n ON (s.resourceNode_id = n.id)
            WHERE NOT EXISTS (
                SELECT *
                FROM claro_resource_evaluation AS r
                LEFT JOIN claro_resource_user_evaluation AS ru ON (r.resource_user_evaluation = ru.id) 
                WHERE ru.resource_node = n.id
                  AND ru.user_id = t.user_id 
            ) 
        ');

        $results = $statement->fetchAllAssociative();

        $output->writeln(sprintf('Found %d ScoTracking to fix.', count($results)));
        foreach ($results as $result) {
            $output->writeln(sprintf('Fixing ScoTracking with ID : %d.', $result['id']));
            $tracking = $this->om->getRepository(ScoTracking::class)->find($result['id']);
            if ($tracking) {
                $this->manager->generateScormEvaluation($tracking);
            }
        }
    }

    private function syncStatuses(OutputInterface $output)
    {
        $statement = $this->connection->query('
            SELECT t.id
            FROM claro_scorm_sco_tracking AS t
            LEFT JOIN claro_scorm_sco AS sco ON (t.sco_id = sco.id)
            LEFT JOIN claro_scorm AS s ON (sco.scorm_id = s.id)
            LEFT JOIN claro_resource_node AS n ON (s.resourceNode_id = n.id)
            WHERE EXISTS (
                SELECT *
                FROM claro_resource_user_evaluation AS ru 
                WHERE ru.resource_node = n.id
                  AND ru.user_id = t.user_id 
                  AND ru.evaluation_status != t.lesson_status
                  AND t.lesson_status != "unknown"
                  AND t.lesson_status != "not attempted"
                  AND t.lesson_status != "browsed"
            ) 
        ');

        $results = $statement->fetchAllAssociative();

        $output->writeln(sprintf('Found %d ScoTracking to sync with ResourceUserEvaluation.', count($results)));
        foreach ($results as $result) {
            $output->writeln(sprintf('Fixing ScoTracking with ID : %d.', $result['id']));
            $tracking = $this->om->getRepository(ScoTracking::class)->find($result['id']);
            if ($tracking) {
                $evaluation = $this->evaluationManager->getUserEvaluation($tracking->getSco()->getScorm()->getResourceNode(), $tracking->getUser());
                if ($evaluation) {
                    $evaluation->setStatus($tracking->getLessonStatus());
                    $this->om->persist($evaluation);
                }
            }
        }

        $this->om->flush();
    }
}
