<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Activity\ActivityParameters;
use Claroline\CoreBundle\Entity\Activity\Evaluation;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @Service("claroline.manager.activity_manager")
 */
class ActivityManager
{
    private $activityRuleRepo;
    private $evaluationRepo;
    private $pastEvaluationRepo;
    private $persistence;
    private $resourceManager;

    /**
     * Constructor.
     *
     * @InjectParams({
     *     "persistence"        = @Inject("claroline.persistence.object_manager"),
     *     "resourceManager"    = @Inject("claroline.manager.resource_manager")
     * })
     */
    public function __construct(
        ObjectManager $persistence,
        ResourceManager $resourceManager
    )
    {
        $this->persistence = $persistence;
        $this->resourceManager = $resourceManager;
        $this->activityRuleRepo = $persistence
            ->getRepository('ClarolineCoreBundle:Activity\ActivityRule');
        $this->evaluationRepo = $persistence
            ->getRepository('ClarolineCoreBundle:Activity\Evaluation');
        $this->pastEvaluationRepo = $persistence
            ->getRepository('ClarolineCoreBundle:Activity\Evaluation');
    }

    /**
     * Create a new activity
     */
    public function createActivity($title, $description, $resourceNodeId, $persist = false)
    {
        $resourceNode = $this->resourceManager->getById($resourceNodeId);
        $parameters = new ActivityParameters();

        return $this->editActivity(new Activity(), $title, $description, $resourceNode, $parameters, $persist);
    }

    /**
     * Edit an activity
     */
    public function editActivity($activity, $title, $description, $resourceNode, $parameters, $persist = false)
    {
        $activity->setName($title);
        $activity->setTitle($title);
        $activity->setDescription($description);
        $activity->setResourceNode($resourceNode);
        $activity->setParameters($parameters);

        if ($persist) {
            $this->persistence->persist($activity);
            $this->persistence->flush();
        }

        return $activity;
    }

    /**
     * Delete an activity
     */
    public function deleteActivty($activity)
    {
        $this->persistence->remove($activity);
        $this->persistence->flush();
    }

    /**
     * Link a resource to an activity
     */
    public function addResource($resourceActivity)
    {
        $this->persistence->persist($resourceActivity);
        $this->persistence->flush();
    }

    /**
     * Edit a resource link in an activity
     */
    public function editResource($resourceActivity)
    {
        $this->persistence->persist($resourceActivity);
        $this->persistence->flush();
    }

    /**
     * delete a resource from an activity
     */
    public function deleteResource($resourceActivity)
    {
        $this->persistence->persist($resourceActivity);
        $this->persistence->flush();
    }

    public function updateEvaluation(
        User $user,
        ActivityParameters $activityParams,
        Log $log,
        $rulesLogs,
        $activityStatus
    )
    {
        $evaluationType = $activityParams->getEvaluationType();
        $maxTimeAllowed = $activityParams->getMaxDuration();
        $maxAttempts = $activityParams->getMaxAttempts();

        $logDetails = $log->getDetails();
        $duration = isset($logDetails['duration']) ?
            $logDetails['duration'] :
            null;
        $totalTime = is_null($duration) ? 0 : $duration;
        $score = isset($logDetails['result']) ?
            $logDetails['result'] :
            null;
        $scoreMin = isset($logDetails['scoreMin']) ?
            $logDetails['scoreMin'] :
            0;
        $scoreMax = isset($logDetails['scoreMax']) ?
            $logDetails['scoreMax'] :
            null;

        $evaluation = $this->evaluationRepo
            ->findEvaluationByUserAndActivityParams($user, $activityParams);

        if (is_null($evaluation)) {
            $firstEvaluation = new Evaluation();
            $firstEvaluation->setUser($user);
            $firstEvaluation->setActivityParameters($activityParams);
            $firstEvaluation->setType($evaluationType);
            $firstEvaluation->setAttemptsCount(1);
            $firstEvaluation->setLog($log);
            $firstEvaluation->setStatus($activityStatus);
            $firstEvaluation->setNumScore($score);
            $firstEvaluation->setScoreMin($scoreMin);
            $firstEvaluation->setScoreMax($scoreMax);
            $firstEvaluation->setDuration($duration);
            $firstEvaluation->setAttemptsDuration($totalTime);

            $this->persistence->persist($firstEvaluation);
            $this->persistence->flush();
        } else {
            $status = $evaluation->getStatus();
            $evaluation->setAttemptsCount($evaluation->getAttemptsCount() + 1);

            // Archiver la tentative
        }
    }


    /*********************************************
     *  Access to ActivityRuleRepository methods *
     *********************************************/

    public function getActivityRuleByActionAndResource(
        $action,
        ResourceNode $resourceNode,
        $executeQuery = true
    )
    {
        return $this->activityRuleRepo->findActivityRuleByActionAndResource(
            $action,
            $resourceNode,
            $executeQuery
        );
    }


    /******************************************
     * Access to EvaluationRepository methods *
     ******************************************/

    public function getEvaluationByUserAndActivityParams(
        User $user,
        ActivityParameters $activityParams,
        $executeQuery = true
    )
    {
        return $this->evaluationRepo->findEvaluationByUserAndActivityParams(
            $user,
            $activityParams,
            $executeQuery
        );
    }


    /******************************************
     * Access to PastEvaluationRepository methods *
     ******************************************/

    public function getPastEvaluationsByUserAndActivityParams(
        User $user,
        ActivityParameters $activityParams,
        $executeQuery = true
    )
    {
        return $this->evaluationRepo->findPastEvaluationsByUserAndActivityParams(
            $user,
            $activityParams,
            $executeQuery
        );
    }
}
