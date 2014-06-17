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
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\User;
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
    private $activityRuleActionRepo;
    private $activityRuleRepo;
    private $evaluationRepo;
    private $pastEvaluationRepo;
    private $om;

    /**
     * @InjectParams({
     *     "om" = @Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->activityRuleActionRepo = $om->getRepository('ClarolineCoreBundle:Activity\ActivityRuleAction');
        $this->activityRuleRepo = $om->getRepository('ClarolineCoreBundle:Activity\ActivityRule');
        $this->evaluationRepo = $om->getRepository('ClarolineCoreBundle:Activity\Evaluation');
        $this->pastEvaluationRepo = $om->getRepository('ClarolineCoreBundle:Activity\PastEvaluation');
    }

    /**
     * Edit an activity
     */
    public function editActivity(Activity $activity)
    {
        $this->om->persist($activity);
        $this->om->flush();

        return $activity;
    }

    /**
     * Delete an activity
     */
    public function deleteActivty(Activity $activity)
    {
        $this->om->remove($activity);
        $this->om->flush();
    }

    /**
     * Link a resource to an activity
     */
    public function addResource(Activity $activity, ResourceNode $resource)
    {
        if (!$activity->getParameters()->getSecondaryResources()->contains($resource)) {
            $activity->getParameters()->getSecondaryResources()->add($resource);
            $this->om->persist($activity);
            $this->om->flush();

            return true;
        }
    }

    /**
     * Remove a resource from an activity
     */
    public function removeResource(Activity $activity, ResourceNode $resource)
    {
        if ($activity->getParameters()->getSecondaryResources()->contains($resource)) {
            $activity->getParameters()->getSecondaryResources()->removeElement($resource);
            $this->om->persist($activity);
            $this->om->flush();

            return true;
        }
    }

    /**
     * Copy an activity
     */
    public function copyActivity(Activity $resource)
    {
        $activity = new Activity();

        $activity->setTitle($resource->getTitle());
        $activity->setDescription($resource->getDescription());
        $activity->setParameters($this->copyParameters($resource));

        if ($primaryResource = $resource->getPrimaryResource()) {
            $activity->setPrimaryResource($primaryResource);
        }

        return $activity;
    }

    /**
     * Copy parameters
     * @todo copy properties
     */
    public function copyParameters(Activity $resource)
    {
        $parameters = new ActivityParameters();

        foreach ($resource->getParameters()->getSecondaryResources() as $resource) {
            $parameters->getSecondaryResources()->add($resource);
        }

        return $parameters;
    }

    public function updateParameters(
        ActivityParameters $params,
        $maxDuration,
        $maxAttempts,
        $evaluationType
    )
    {
        $params->setMaxDuration($maxDuration);
        $params->setMaxAttempts($maxAttempts);
        $params->setEvaluationType($evaluationType);
        $this->om->persist($params);
        $this->om->flush();
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

                    $this->om->persist($pastEval);
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

            $this->om->persist($pastEval);
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

        $this->om->persist($firstEvaluation);
        $this->om->flush();
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

                        if (!is_null($pastEval->getLog()) &&
                            $pastEval->getLog()->getId() === $log->getId()) {

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

                        $this->om->persist($pastEval);
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

            $this->om->persist($pastEval);
        }

        $evaluation->setDate($currentLog->getDateLog());
        $evaluation->setAttemptsCount($nbAttempts);
        $evaluation->setAttemptsDuration($currentTotalTime);
        $evaluation->setStatus($activityStatus);
        $evaluation->setLog($currentLog);

        $this->om->persist($evaluation);
        $this->om->flush();
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

        $this->om->persist($rule);
        $this->om->flush();
    }

    public function updateActivityRule(
        ActivityRule $rule,
        $action,
        $occurrence,
        $result,
        $activeFrom,
        $activeUntil,
        ResourceNode $resourceNode = null
    )
    {
        $rule->setAction($action);
        $rule->setOccurrence($occurrence);
        $rule->setResult($result);
        $rule->setActiveFrom($activeFrom);
        $rule->setActiveUntil($activeUntil);
        $rule->setResource($resourceNode);

        $this->om->persist($rule);
        $this->om->flush();
    }

    public function deleteActivityRule(ActivityRule $rule)
    {
        $this->om->remove($rule);
        $this->om->flush();
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


    /***************************************************
     *  Access to ActivityRuleActionRepository methods *
     ***************************************************/

    public function getAllRuleActions()
    {
        return $this->activityRuleActionRepo->findAll();
    }

    public function getRuleActionsByResourceType(
        ResourceType $resourceType = null,
        $executeQuery = true
    )
    {
        return $this->activityRuleActionRepo
            ->findRuleActionsByResourceType($resourceType, $executeQuery);
    }

    public function getAllDistinctActivityRuleActions($executeQuery = true)
    {
        return $this->activityRuleActionRepo
            ->findAllDistinctActivityRuleActions($executeQuery);
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
