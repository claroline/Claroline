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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Activity\AbstractEvaluation;
use Claroline\CoreBundle\Entity\Activity\ActivityParameters;
use Claroline\CoreBundle\Entity\Activity\ActivityRule;
use Claroline\CoreBundle\Entity\Activity\Evaluation;
use Claroline\CoreBundle\Entity\Activity\PastEvaluation;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Service("claroline.manager.activity_manager")
 */
class ActivityManager
{
    private $activityParametersRepo;
    private $activityRepo;
    private $activityRuleActionRepo;
    private $activityRuleRepo;
    private $evaluationRepo;
    private $pastEvaluationRepo;
    private $roleRepo;
    private $om;
    private $rightsManager;
    private $tokenStorage;
    private $dispatcher;

    /**
     * @InjectParams({
     *     "om"            = @Inject("claroline.persistence.object_manager"),
     *     "rightsManager" = @Inject("claroline.manager.rights_manager"),
     *     "tokenStorage"  = @Inject("security.token_storage"),
     *     "dispatcher"    = @Inject("claroline.event.event_dispatcher")
     * })
     */
    public function __construct(
        ObjectManager            $om,
        RightsManager            $rightsManager,
        TokenStorageInterface    $tokenStorage,
        StrictDispatcher         $dispatcher
    ) {
        $this->om = $om;
        $this->activityParametersRepo = $om->getRepository('ClarolineCoreBundle:Activity\ActivityParameters');
        $this->activityRepo = $om->getRepository('ClarolineCoreBundle:Resource\Activity');
        $this->activityRuleActionRepo = $om->getRepository('ClarolineCoreBundle:Activity\ActivityRuleAction');
        $this->activityRuleRepo = $om->getRepository('ClarolineCoreBundle:Activity\ActivityRule');
        $this->evaluationRepo = $om->getRepository('ClarolineCoreBundle:Activity\Evaluation');
        $this->pastEvaluationRepo = $om->getRepository('ClarolineCoreBundle:Activity\PastEvaluation');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->rightsManager = $rightsManager;
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Edit an activity.
     */
    public function editActivity(Activity $activity)
    {
        $this->om->persist($activity);
        $this->om->flush();
        $this->initializePermissions($activity);

        return $activity;
    }

    /**
     * Delete an activity.
     */
    public function deleteActivty(Activity $activity)
    {
        $this->om->remove($activity);
        $this->om->flush();
    }

    /**
     * Link a resource to an activity.
     */
    public function addResource(Activity $activity, ResourceNode $resource)
    {
        if (!$activity->getParameters()->getSecondaryResources()->contains($resource)) {
            $activity->getParameters()->getSecondaryResources()->add($resource);
            $this->initializePermissions($activity);
            $this->om->persist($activity);
            $this->om->flush();

            return true;
        }
    }

    /**
     * Remove the primary resource of an activity.
     */
    public function removePrimaryResource(Activity $activity)
    {
        $activity->setPrimaryResource();
        $this->om->persist($activity);
        $this->om->flush();
    }

    /**
     * Remove a resource from an activity.
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
     * Copy an activity.
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
     * Copy parameters.
     *
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
    ) {
        $params->setMaxDuration($maxDuration);
        $params->setMaxAttempts($maxAttempts);
        $params->setEvaluationType($evaluationType);
        $this->om->persist($params);
        $this->om->flush();
    }

    public function manageEvaluation(
        User $user,
        ActivityParameters $activityParams,
        Log $currentLog,
        $rulesLogs,
        $activityStatus
    ) {
        $evaluation = $this->evaluationRepo
            ->findEvaluationByUserAndActivityParams($user, $activityParams);
        $isFirstEvaluation = is_null($evaluation);
        $evaluationType = $activityParams->getEvaluationType();
        $ruleScore = null;
        $ruleScoreMax = null;

        if (AbstractEvaluation::TYPE_AUTOMATIC === $evaluationType
            && count($activityParams->getRules()) > 0) {
            $rule = $activityParams->getRules()->first();
            $ruleScore = $rule->getResult();
            $ruleScoreMax = $rule->getResultMax();
        }

        if (!$isFirstEvaluation) {
            $pastEvals = $this->pastEvaluationRepo
                ->findPastEvaluationsByUserAndActivityParams($user, $activityParams);
        }

        $nbAttempts = $isFirstEvaluation ? 0 : count($pastEvals);
        $totalTime = $isFirstEvaluation ? null : $evaluation->getAttemptsDuration();
        $pastStatus = AbstractEvaluation::STATUS_INCOMPLETE === $activityStatus
            || AbstractEvaluation::STATUS_FAILED === $activityStatus ?
            $activityStatus :
            AbstractEvaluation::STATUS_UNKNOWN;
        $previousStatus = $evaluation ?
            $evaluation->getStatus() :
            AbstractEvaluation::STATUS_UNKNOWN;

        if (isset($rulesLogs['rules']) && is_array($rulesLogs['rules'])) {
            foreach ($rulesLogs['rules'] as $ruleLogs) {
                $logs = $ruleLogs['logs'];

                foreach ($logs as $log) {
                    $pastEvalExisted = false;

                    // Checks if this archived log is the same as the log
                    // that triggers the evaluation
                    if ($log->getId() === $currentLog->getId()) {
                        break;
                    }

                    // Checks if the log is already associated to an existing
                    // PastEvaluation
                    if (!$isFirstEvaluation) {
                        foreach ($pastEvals as $pastEval) {
                            if (!is_null($pastEval->getLog()) &&
                                $pastEval->getLog()->getId() === $log->getId()) {
                                $pastEvalExisted = true;
                                break;
                            }
                        }
                    }

                    // If the log isn't associated to an existing PastEvaluation
                    if (!$pastEvalExisted) {
                        $logDetails = $log->getDetails();
                        $duration = isset($logDetails['duration']) ?
                            $logDetails['duration'] :
                            null;
                        $score = isset($logDetails['result']) ?
                            $logDetails['result'] :
                            null;
                        $scoreMin = isset($logDetails['resultMin']) ?
                            $logDetails['resultMin'] :
                            null;
                        $scoreMax = isset($logDetails['resultMax']) ?
                            $logDetails['resultMax'] :
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
                        $pastEval->setStatus($pastStatus);

                        ++$nbAttempts;
                        $totalTime = $this->computeActivityTotalTime(
                            $totalTime,
                            $duration
                        );
                        $this->om->persist($pastEval);
                    }
                }
            }
        }

        // Creates a PastEvaluation for the log that triggers the evaluation
        $logDetails = $currentLog->getDetails();
        $duration = isset($logDetails['duration']) ?
            $logDetails['duration'] :
            null;
        $score = isset($logDetails['result']) ?
            $logDetails['result'] :
            null;
        $scoreMin = isset($logDetails['resultMin']) ?
            $logDetails['resultMin'] :
            null;
        $scoreMax = isset($logDetails['resultMax']) ?
            $logDetails['resultMax'] :
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

        if ((AbstractEvaluation::STATUS_COMPLETED === $activityStatus
            || AbstractEvaluation::STATUS_PASSED === $activityStatus)
            && !is_null($score)
            && !is_null($ruleScore)) {
            $realStatus = $this->hasPassingScore($ruleScore, $ruleScoreMax, $score, $scoreMax) ?
                $activityStatus :
                AbstractEvaluation::STATUS_FAILED;
            $pastEval->setStatus($realStatus);
        } else {
            $pastEval->setStatus($activityStatus);
        }

        ++$nbAttempts;
        $totalTime = $this->computeActivityTotalTime($totalTime, $duration);
        $this->om->persist($pastEval);

        if ($isFirstEvaluation) {
            $evaluation = new Evaluation();
            $evaluation->setUser($user);
            $evaluation->setActivityParameters($activityParams);
            $evaluation->setType($evaluationType);
            $evaluation->setNumScore($score);
            $evaluation->setScoreMin($scoreMin);
            $evaluation->setScoreMax($scoreMax);
        } else {
            $this->persistBestScore($evaluation, $score, $scoreMin, $scoreMax);
        }
        $evaluation->setDate($currentLog->getDateLog());
        $evaluation->setAttemptsCount($nbAttempts);
        $evaluation->setAttemptsDuration($totalTime);
        $evaluation->setStatus($activityStatus);
        $evaluation->setLog($currentLog);

        $this->om->persist($evaluation);
        $this->om->flush();

        if ($activityStatus !== $previousStatus && $evaluation->isTerminated()) {
            $this->dispatchEvaluation($evaluation);
        }
    }

    public function createActivityRule(
        ActivityParameters $activityParams,
        $action,
        $occurrence,
        $result,
        $resultMax,
        $isResultVisible,
        $activeFrom,
        $activeUntil,
        ResourceNode $resourceNode = null
    ) {
        $rule = new ActivityRule();
        $rule->setActivityParameters($activityParams);
        $rule->setAction($action);
        $rule->setOccurrence($occurrence);
        $rule->setResult($result);
        $rule->setResultMax($resultMax);
        $rule->setIsResultVisible($isResultVisible);
        $rule->setActiveFrom($activeFrom);
        $rule->setActiveUntil($activeUntil);
        $rule->setResource($resourceNode);
        $rule->setUserType(0);
        $rule->setResultComparison(4);

        $this->om->persist($rule);
        $this->om->flush();
    }

    public function updateActivityRule(
        ActivityRule $rule,
        $action,
        $occurrence,
        $result,
        $resultMax,
        $isResultVisible,
        $activeFrom,
        $activeUntil,
        ResourceNode $resourceNode = null
    ) {
        $rule->setAction($action);
        $rule->setOccurrence($occurrence);
        $rule->setResult($result);
        $rule->setResultMax($resultMax);
        $rule->setIsResultVisible($isResultVisible);
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

    /**
     * Creates an empty activity evaluation for a user, so that an evaluation
     * is available for display and edition even when the user hasn't actually
     * performed the activity.
     *
     * @param User               $user
     * @param ActivityParameters $activityParams
     *
     * @return Evaluation
     */
    public function createBlankEvaluation(User $user, ActivityParameters $activityParams)
    {
        $evaluationType = $activityParams->getEvaluationType();
        $status = null;
        $nbAttempts = null;

        if (AbstractEvaluation::TYPE_AUTOMATIC === $evaluationType) {
            $status = AbstractEvaluation::STATUS_NOT_ATTEMPTED;
            $nbAttempts = 0;
        }

        $evaluation = new Evaluation();
        $evaluation->setUser($user);
        $evaluation->setActivityParameters($activityParams);
        $evaluation->setType($evaluationType);
        $evaluation->setStatus($status);
        $evaluation->setAttemptsCount($nbAttempts);

        $this->om->persist($evaluation);
        $this->om->flush();

        return $evaluation;
    }

    private function computeActivityTotalTime($totalTime, $sessionTime)
    {
        if (!is_null($totalTime) && !is_null($sessionTime)) {
            $result = $totalTime + $sessionTime;
        } elseif (!is_null($totalTime)) {
            $result = $totalTime;
        } else {
            $result = $sessionTime;
        }

        return $result;
    }

    private function persistBestScore(
        Evaluation $evaluation,
        $score,
        $scoreMin,
        $scoreMax
    ) {
        if (!is_null($score)) {
            $currentScore = $evaluation->getNumScore();
            $currentScoreMax = $evaluation->getScoreMax();
            $updateScore = false;

            if (is_null($currentScore)) {
                $updateScore = true;
            } elseif (empty($currentScoreMax) || empty($scoreMax)) {
                $updateScore = ($score > $currentScore);
            } else {
                $realCurrentScore = number_format(
                    round($currentScore / $currentScoreMax, 2),
                    2
                );
                $realScore = number_format(
                    round($score / $scoreMax, 2),
                    2
                );
                $updateScore = ($realScore > $realCurrentScore);
            }

            if ($updateScore) {
                $evaluation->setNumScore($score);
                $evaluation->setScoreMin($scoreMin);
                $evaluation->setScoreMax($scoreMax);
                $this->om->persist($evaluation);
            }
        }
    }

    public function editEvaluation(Evaluation $evaluation)
    {
        $this->updatePastEvaluation($evaluation);
        $this->om->persist($evaluation);
        $this->om->flush();

        if ($evaluation->isTerminated()) {
            $this->dispatchEvaluation($evaluation);
        }
    }

    public function editPastEvaluation(PastEvaluation $pastEvaluation)
    {
        $this->om->persist($pastEvaluation);
        $this->om->flush();
    }

    private function updatePastEvaluation(Evaluation $evaluation)
    {
        $user = $evaluation->getUser();
        $activityParams = $evaluation->getActivityParameters();
        $log = $evaluation->getLog();

        if (!is_null($log)) {
            $pastEval = $this->pastEvaluationRepo
                ->findPastEvaluationsByUserAndActivityParamsAndLog(
                    $user,
                    $activityParams,
                    $log
                );

            if (!is_null($pastEval)) {
                $pastEval->setScore($evaluation->getScore());
                $pastEval->setComment($evaluation->getComment());
                $this->om->persist($pastEval);
            }
        }
    }

    private function hasPassingScore(
        $ruleScore,
        $ruleScoreMax,
        $score,
        $scoreMax
    ) {
        $hasPassingScore = true;

        if (!is_null($ruleScore) && !is_null($score)) {
            if (empty($ruleScoreMax) || empty($scoreMax)) {
                $hasPassingScore = ($score >= $ruleScore);
            } else {
                $realRuleScore = number_format(
                    round($ruleScore / $ruleScoreMax, 2),
                    2
                );
                $realScore = number_format(
                    round($score / $scoreMax, 2),
                    2
                );
                $hasPassingScore = ($realScore >= $realRuleScore);
            }
        } elseif (!is_null($ruleScore)) {
            $hasPassingScore = false;
        }

        return $hasPassingScore;
    }

    /*****************************************
     *  Access to ActivityRepository methods *
     *****************************************/

    public function getActivityByWorkspace(
        Workspace $workspace,
        $executeQuery = true
    ) {
        return $this->activityRepo->findActivityByWorkspace(
            $workspace,
            $executeQuery
        );
    }

    public function getActivitiesByResourceNodeIds(
        array $resourceNodeIds,
        $executeQuery = true
    ) {
        if (count($resourceNodeIds) > 0) {
            return $this->activityRepo->findActivitiesByResourceNodeIds(
                $resourceNodeIds,
                $executeQuery
            );
        }

        return [];
    }

    /*********************************************
     *  Access to ActivityRuleRepository methods *
     *********************************************/

    public function getActivityRuleByActionAndResource(
        $action,
        ResourceNode $resourceNode,
        $executeQuery = true
    ) {
        return $this->activityRuleRepo->findActivityRuleByActionAndResource(
            $action,
            $resourceNode,
            $executeQuery
        );
    }

    public function getActivityRuleByActionWithNoResource(
        $action,
        $executeQuery = true
    ) {
        return $this->activityRuleRepo->findActivityRuleByActionWithNoResource(
            $action,
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
    ) {
        return $this->activityRuleActionRepo
            ->findRuleActionsByResourceType($resourceType, $executeQuery);
    }

    public function getRuleActionsWithNoResourceType($executeQuery = true)
    {
        return $this->activityRuleActionRepo
            ->findRuleActionsWithNoResourceType($executeQuery);
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
    ) {
        return $this->evaluationRepo->findEvaluationByUserAndActivityParams(
            $user,
            $activityParams,
            $executeQuery
        );
    }

    public function getEvaluationsByUserAndActivityParameters(
        User $user,
        array $activityParams,
        $executeQuery = true
    ) {
        if (count($activityParams) > 0) {
            return $this->evaluationRepo->findEvaluationsByUserAndActivityParameters(
                $user,
                $activityParams,
                $executeQuery
            );
        }

        return [];
    }

    public function getEvaluationsByUsersAndActivityParams(
        array $users,
        ActivityParameters $activityParams,
        $executeQuery = true
    ) {
        if (count($users) > 0) {
            return $this->evaluationRepo->findEvaluationsByUsersAndActivityParams(
                $users,
                $activityParams,
                $executeQuery
            );
        }

        return [];
    }

    /**********************************************
     * Access to PastEvaluationRepository methods *
     **********************************************/

    public function getPastEvaluationsByUserAndActivityParams(
        User $user,
        ActivityParameters $activityParams,
        $executeQuery = true
    ) {
        return $this->pastEvaluationRepo->findPastEvaluationsByUserAndActivityParams(
            $user,
            $activityParams,
            $executeQuery
        );
    }

    public function getPastEvaluationsByUserAndActivityParamsAndLog(
        User $user,
        ActivityParameters $activityParams,
        Log $log,
        $executeQuery = true
    ) {
        return $this->pastEvaluationRepo
            ->findPastEvaluationsByUserAndActivityParamsAndLog(
                $user,
                $activityParams,
                $log,
                $executeQuery
            );
    }

    /**
     * What does it do ? I can't remember. It's annoying.
     * Initialize the resource permissions of an activity.
     *
     * @param Activity $activity
     */
    public function initializePermissions(Activity $activity)
    {
        $primary = $activity->getPrimaryResource();
        $secondaries = [];
        $nodes = [];
        $token = $this->tokenStorage->getToken();
        $user = null === $token ? $activity->getResourceNode()->getCreator() : $token->getUser();

        if ($primary) {
            $nodes[] = $primary;
        }

        foreach ($activity->getParameters()->getSecondaryResources() as $res) {
            $secondaries[] = $res;
        }

        $nodes = array_merge($nodes, $secondaries);
        $nodesInitialized = [];

        foreach ($nodes as $node) {
            $isNodeCreator = $node->getCreator() === $user;
            $ws = $node->getWorkspace();
            $roleWsManager = $this->roleRepo->findManagerRole($ws);
            $isWsManager = $user->hasRole($roleWsManager);

            if ($isNodeCreator || $isWsManager) {
                $nodesInitialized[] = $node;
            }
        }

        $rolesInitialized = [];
        $rights = $activity->getResourceNode()->getRights();

        foreach ($rights as $right) {
            $role = $right->getRole();

            if (!strpos('_'.$role->getName(), 'ROLE_WS_MANAGER')
                //the open value is always 1
                && $right->getMask() & 1
            ) {
                $rolesInitialized[] = $role;
            }
        }

        $this->rightsManager->initializePermissions($nodesInitialized, $rolesInitialized);
    }

    public function addPermissionsToResource(Activity $activity, array $roles)
    {
        $primary = $activity->getPrimaryResource();
        $secondaries = [];
        $nodes = [];

        if ($primary) {
            $nodes[] = $primary;
        }

        foreach ($activity->getParameters()->getSecondaryResources() as $res) {
            $secondaries[] = $res;
        }

        $nodes = array_merge($nodes, $secondaries);

        $this->rightsManager->initializePermissions($nodes, $roles);
    }

    private function dispatchEvaluation(Evaluation $evaluation)
    {
        $this->dispatcher->dispatch(
            'activity_evaluation',
            'ActivityEvaluation',
            [$evaluation]
        );
    }
}
