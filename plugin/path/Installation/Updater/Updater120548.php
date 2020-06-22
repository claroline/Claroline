<?php

namespace Innova\PathBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Driver\Connection;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Manager\UserProgressionManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120548 extends Updater
{
    /** @var ContainerInterface */
    private $container;

    /** @var Connection */
    private $connection;

    /** @var ObjectManager */
    private $om;

    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;

    /** @var UserProgressionManager */
    private $userProgressionManager;

    public function __construct($container)
    {
        $this->container = $container;

        $this->connection = $this->container->get('doctrine.dbal.default_connection');
        $this->om = $this->container->get(ObjectManager::class);
        $this->resourceEvalManager = $this->container->get('claroline.manager.resource_evaluation_manager');
        $this->userProgressionManager = $this->container->get(UserProgressionManager::class);
    }

    public function postUpdate()
    {
        $this->fixDuplicateResourceEvaluation();
    }

    /**
     * Initializes default score to success.
     */
    private function fixDuplicateResourceEvaluation()
    {
        $this->log('Fixes path progression');

        // find all path ResourceUserEvaluation with multiple ResourceEvaluation
        $userEvaluations = $this->connection
            ->query('
                SELECT e.resource_user_evaluation AS id, ue.user_id, n.id AS node_id, p.id AS path_id, COUNT(ue.id) AS count 
                FROM claro_resource_evaluation AS e 
                LEFT JOIN claro_resource_user_evaluation AS ue ON (e.resource_user_evaluation = ue.id)
                LEFT JOIN claro_resource_node AS n ON (ue.resource_node = n.id)
                LEFT JOIN innova_path AS p ON (n.id = p.resourceNode_id)
                WHERE n.mime_type = "custom/innova_path"
                GROUP BY id
                HAVING count > 1
            ')
            ->fetchAll();

        $this->log(sprintf('Found %d ResourceUserEvaluation to recompute...', count($userEvaluations)));

        foreach ($userEvaluations as $userEvaluation) {
            $this->log(sprintf('Recomputing evaluation "%d" for user "%d"', $userEvaluation['id'], $userEvaluation['user_id']));

            // remove all ResourceEvaluation
            $this->connection
                ->prepare("
                    DELETE FROM claro_resource_evaluation WHERE resource_user_evaluation = {$userEvaluation['id']}
                ")
                ->execute();

            // find StepProgression for the user
            $stepProgressions = $this->connection
                ->query("
                    SELECT s.uuid, p.progression_status AS status
                    FROM innova_path_progression AS p
                    LEFT JOIN innova_step AS s ON (p.step_id = s.id AND s.path_id = {$userEvaluation['path_id']})
                    WHERE s.id IS NOT NULL
                ")
                ->fetchAll();

            // compute progression
            $data = ['done' => []];
            foreach ($stepProgressions as $stepProgression) {
                if (in_array($stepProgression['status'], ['seen', 'done'])) {
                    // mark the step as done if it has the correct status
                    $data['done'][] = $stepProgression['uuid'];
                }
            }

            $statusData = $this->userProgressionManager->computeResourceUserEvaluation(
                $this->om->getRepository(Path::class)->find($userEvaluation['path_id']),
                $data
            );

            $evaluationData = [
                'status' => $statusData['status'],
                'progression' => $statusData['progression'],
                'progressionMax' => $statusData['progressionMax'],
                'data' => $data,
            ];

            // create a new ResourceEvaluation from StepProgression
            // and recompute ResourceUserEvaluation (automatically done when a new ResourceEvaluation is created)
            $this->resourceEvalManager->createResourceEvaluation(
                $this->om->getRepository(ResourceNode::class)->find($userEvaluation['node_id']),
                $this->om->getRepository(User::class)->find($userEvaluation['user_id']),
                null,
                $evaluationData
            );
        }
    }
}
