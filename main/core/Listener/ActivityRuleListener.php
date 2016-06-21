<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Entity\Activity\AbstractEvaluation;
use Claroline\CoreBundle\Event\LogCreateEvent;
use Claroline\CoreBundle\Manager\ActivityManager;
use Claroline\CoreBundle\Rule\Validator;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class ActivityRuleListener
{
    private $activityManager;
    private $ruleValidator;

    /**
     * @DI\InjectParams({
     *     "activityManager" = @DI\Inject("claroline.manager.activity_manager"),
     *     "ruleValidator"   = @DI\Inject("claroline.rule.validator")
     * })
     */
    public function __construct(
        ActivityManager $activityManager,
        Validator $ruleValidator
    ) {
        $this->activityManager = $activityManager;
        $this->ruleValidator = $ruleValidator;
    }

    /**
     * @DI\Observe("claroline.log.create")
     *
     * @param \Claroline\CoreBundle\Event\LogCreateEvent $event
     */
    public function onLog(LogCreateEvent $event)
    {
        $log = $event->getLog();
        $dateLog = $log->getDateLog();
        $action = $log->getAction();
        $resourceNode = $log->getResourceNode();
        $user = $log->getDoer();

        if (!is_null($user)) {
            $activityRules = is_null($resourceNode) ?
                $this->activityManager
                    ->getActivityRuleByActionWithNoResource($action) :
                $this->activityManager
                    ->getActivityRuleByActionAndResource($action, $resourceNode);

            if (count($activityRules) > 0) {
                foreach ($activityRules as $activityRule) {
                    $activityParams = $activityRule->getActivityParameters();
                    $accessFrom = $activityRule->getActiveFrom();
                    $accessUntil = $activityRule->getActiveUntil();

                    if ((is_null($accessFrom) || $dateLog >= $accessFrom)
                        && (is_null($accessUntil) || $dateLog <= $accessUntil)) {
                        $nbRules = is_null($activityParams->getRules()) ?
                            0 :
                            count($activityParams->getRules());

                        if ($nbRules > 0) {
                            $rules = $activityParams->getRules();
                            $ruleType = 'result';

                            foreach ($rules as $rule) {
                                if (is_null($rule->getResult())) {
                                    $ruleType = 'occurrence';
                                    break;
                                }
                            }

                            $rulesLogs = $this->ruleValidator->validate($activityParams, $user);

                            if (isset($rulesLogs['validRules'])
                                && $rulesLogs['validRules'] >= $nbRules) {
                                $activityStatus = $ruleType === 'occurrence' ?
                                    AbstractEvaluation::STATUS_COMPLETED :
                                    AbstractEvaluation::STATUS_PASSED;
                            } else {
                                $activityStatus = $ruleType === 'occurrence' ?
                                    AbstractEvaluation::STATUS_INCOMPLETE :
                                    AbstractEvaluation::STATUS_FAILED;
                            }

                            $this->activityManager->manageEvaluation(
                                $user,
                                $activityParams,
                                $log,
                                $rulesLogs,
                                $activityStatus
                            );
                        }
                    }
                }
            }
        }
    }
}
