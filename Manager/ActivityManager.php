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
use Claroline\CoreBundle\Entity\Activity\ActivityRule;
use Claroline\CoreBundle\Entity\Activity\Evaluation;
use Claroline\CoreBundle\Entity\Activity\PastEvaluation;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Rule\Entity\Rule;
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

    public function manageEvaluation(
        User $user,
        ActivityParameters $activityParams,
        Log $log,
        $rulesLogs,
        $activityStatus
    )
    {
        $evaluation = $this->evaluationRepo
            ->findEvaluationByUserAndActivityParams($user, $activityParams);

        if (is_null($evaluation)) {
            $this->manageFirstEvaluation(
                $user,
                $activityParams,
                $log,
                $rulesLogs,
                $activityStatus
            );
        } else {
            $this->updateEvaluation(
                $evaluation,
                $user,
                $activityParams,
                $log,
                $rulesLogs,
                $activityStatus
            );

            // Archiver la tentative
        }
    }

    private function manageFirstEvaluation(
        User $user,
        ActivityParameters $activityParams,
        Log $currentLog,
        $rulesLogs,
        $activityStatus
    )
    {
        $evaluationType = $activityParams->getEvaluationType();
//        $maxTimeAllowed = $activityParams->getMaxDuration();
//        $maxAttempts = $activityParams->getMaxAttempts();
        $nbAttempts = 0;
        $totalTime = 0;
        $processCurrentLog = true;

        if (isset($rulesLogs['rules']) && is_array($rulesLogs['rules'])) {

            foreach ($rulesLogs['rules'] as $ruleLogs) {
//                $rule = $ruleLogs['rule'];
                $logs = $ruleLogs['logs'];

                foreach ($logs as $log) {

                    if ($log->getId() === $currentLog->getId()) {
                        $processCurrentLog = false;
                    }
                    $logDetails = $log->getDetails();
                    $duration = isset($logDetails['duration']) ?
                        $logDetails['duration'] :
                        null;
                    $score = isset($logDetails['result']) ?
                        $logDetails['result'] :
                        null;
                    $scoreMin = isset($logDetails['scoreMin']) ?
                        $logDetails['scoreMin'] :
                        0;
                    $scoreMax = isset($logDetails['scoreMax']) ?
                        $logDetails['scoreMax'] :
                        null;

                    $pastEval = new PastEvaluation();
                    $pastEval->setUser($user);
                    $pastEval->setActivityParameters($activityParams);
                    $pastEval->setLog($log);
                    $pastEval->setType($evaluationType);
                    $pastEval->setDate($log->getDateLog());
                    $pastEval->setNumScore($score);
                    $pastEval->setScoreMin($scoreMin);
                    $pastEval->setScoreMax($scoreMax);
                    $pastEval->setDuration($duration);
                    $pastEval->setStatus('unknown');

                    $nbAttempts++;
                    $totalTime = $this->computeActivityTotalTime(
                        $totalTime,
                        $duration
                    );

                    // Checker si la tentative est réussie ou non
                    // Faire qqch avec le nombre max de tentative

                    $this->persistence->persist($pastEval);
                }
            }
        }

        if ($processCurrentLog) {
            $logDetails = $currentLog->getDetails();
            $duration = isset($logDetails['duration']) ?
                $logDetails['duration'] :
                null;
            $score = isset($logDetails['result']) ?
                $logDetails['result'] :
                null;
            $scoreMin = isset($logDetails['scoreMin']) ?
                $logDetails['scoreMin'] :
                0;
            $scoreMax = isset($logDetails['scoreMax']) ?
                $logDetails['scoreMax'] :
                null;

            $pastEval = new PastEvaluation();
            $pastEval->setUser($user);
            $pastEval->setActivityParameters($activityParams);
            $pastEval->setLog($currentLog);
            $pastEval->setType($evaluationType);
            $pastEval->setDate($currentLog->getDateLog());
            $pastEval->setNumScore($score);
            $pastEval->setScoreMin($scoreMin);
            $pastEval->setScoreMax($scoreMax);
            $pastEval->setDuration($duration);
            $pastEval->setStatus($activityStatus);

            $nbAttempts++;
            $totalTime = $this->computeActivityTotalTime(
                $totalTime,
                $duration
            );

            $this->persistence->persist($pastEval);
        }

        $firstEvaluation = new Evaluation();
        $firstEvaluation->setUser($user);
        $firstEvaluation->setActivityParameters($activityParams);
        $firstEvaluation->setType($evaluationType);
        $firstEvaluation->setAttemptsCount($nbAttempts);
        $firstEvaluation->setLog($currentLog);
        $firstEvaluation->setStatus($activityStatus);
//        $firstEvaluation->setNumScore($score);
//        $firstEvaluation->setScoreMin($scoreMin);
//        $firstEvaluation->setScoreMax($scoreMax);
//        $firstEvaluation->setDuration($duration);
        $firstEvaluation->setAttemptsDuration($totalTime);

        $this->persistence->persist($firstEvaluation);
        $this->persistence->flush();
    }

    private function updateEvaluation(
        Evaluation $evaluation,
        User $user,
        ActivityParameters $activityParams,
        Log $currentLog,
        $rulesLogs,
        $activityStatus
    )
    {
        $pastEvals = $this->pastEvaluationRepo
            ->findPastEvaluationsByUserAndActivityParams($user, $activityParams);

        $evaluationType = $activityParams->getEvaluationType();
//        $maxTimeAllowed = $activityParams->getMaxDuration();
//        $maxAttempts = $activityParams->getMaxAttempts();
        $nbAttempts = count($pastEvals);
//        $currentStatus = $evaluation->getStatus();
//        $currentScore = $evaluation->getNumScore();
//        $currentScoreMin = $evaluation->getScoreMin();
//        $currentScoreMax = $evaluation->getScoreMax();
        $currentTotalTime = $evaluation->getAttemptsDuration();
        $processCurrentLog = true;

        if (isset($rulesLogs['rules']) && is_array($rulesLogs['rules'])) {

            foreach ($rulesLogs['rules'] as $ruleLogs) {
                $logs = $ruleLogs['logs'];

                foreach ($logs as $log) {
                    $pastEvalExisted = false;

                    if ($log->getId() === $currentLog->getId()) {
                        $processCurrentLog = false;
                    }

                    foreach ($pastEvals as $pastEval) {

                        if ($pastEval->getLog()->getId() === $log->getId()) {
                            $pastEvalExisted = true;
                            break;
                        }
                    }

                    if (!$pastEvalExisted) {
                        $logDetails = $log->getDetails();
                        $duration = isset($logDetails['duration']) ?
                            $logDetails['duration'] :
                            null;
                        $score = isset($logDetails['result']) ?
                            $logDetails['result'] :
                            null;
                        $scoreMin = isset($logDetails['scoreMin']) ?
                            $logDetails['scoreMin'] :
                            0;
                        $scoreMax = isset($logDetails['scoreMax']) ?
                            $logDetails['scoreMax'] :
                            null;

                        $pastEval = new PastEvaluation();
                        $pastEval->setUser($user);
                        $pastEval->setActivityParameters($activityParams);
                        $pastEval->setLog($log);
                        $pastEval->setType($evaluationType);
                        $pastEval->setDate($log->getDateLog());
                        $pastEval->setNumScore($score);
                        $pastEval->setScoreMin($scoreMin);
                        $pastEval->setScoreMax($scoreMax);
                        $pastEval->setDuration($duration);
                        $pastEval->setStatus('unknown');

                        $nbAttempts++;
                        $currentTotalTime = $this->computeActivityTotalTime(
                            $currentTotalTime,
                            $duration
                        );

                        $this->persistence->persist($pastEval);
                    }
                }
            }
        }
        
        if ($processCurrentLog) {
            $logDetails = $currentLog->getDetails();
            $duration = isset($logDetails['duration']) ?
                $logDetails['duration'] :
                null;
            $score = isset($logDetails['result']) ?
                $logDetails['result'] :
                null;
            $scoreMin = isset($logDetails['scoreMin']) ?
                $logDetails['scoreMin'] :
                0;
            $scoreMax = isset($logDetails['scoreMax']) ?
                $logDetails['scoreMax'] :
                null;

            $pastEval = new PastEvaluation();
            $pastEval->setUser($user);
            $pastEval->setActivityParameters($activityParams);
            $pastEval->setLog($currentLog);
            $pastEval->setType($evaluationType);
            $pastEval->setDate($currentLog->getDateLog());
            $pastEval->setNumScore($score);
            $pastEval->setScoreMin($scoreMin);
            $pastEval->setScoreMax($scoreMax);
            $pastEval->setDuration($duration);
            $pastEval->setStatus($activityStatus);

            $nbAttempts++;
            $currentTotalTime = $this->computeActivityTotalTime(
                $currentTotalTime,
                $duration
            );

            $this->persistence->persist($pastEval);
        }

        $evaluation->setDate($currentLog->getDateLog());
        $evaluation->setAttemptsCount($nbAttempts);
        $evaluation->setAttemptsDuration($currentTotalTime);
        $evaluation->setStatus($activityStatus);
        $evaluation->setLog($currentLog);

        $this->persistence->persist($evaluation);
        $this->persistence->flush();
    }

    private function computeActivityTotalTime($totalTime, $sessionTime)
    {
        $total = is_null($totalTime) ? 0 : $totalTime;
        $session = is_null($sessionTime) ? 0 : $sessionTime;

        return $total + $session;
    }


    public function createActivityRule(
        ActivityParameters $activityParams,
        $action,
        $occurrence,
        $result,
        $activeFrom,
        $activeUntil,
        ResourceNode $resourceNode = null)
    {
        $rule = new ActivityRule();
        $rule->setActivityParameters($activityParams);
        $rule->setAction($action);
        $rule->setOccurrence($occurrence);
        $rule->setResult($result);
        $rule->setActiveFrom($activeFrom);
        $rule->setActiveUntil($activeUntil);
        $rule->setResource($resourceNode);
        $rule->setUserType(0);
        $rule->setResultComparison(Rule::RESULT_SUPERIOR_EQUAL);

        $this->persistence->persist($rule);
        $this->persistence->flush();
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
