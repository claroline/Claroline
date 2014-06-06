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
    )
    {
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
        $action = $log->getAction();
        $resourceNode = $log->getResourceNode();

        if (!is_null($resourceNode)) {
            $activityRules = $this->activityManager
                ->getActivityRuleByActionAndResource($action, $resourceNode);

            if (count($activityRules) > 0) {
                $user =  $log->getDoer();

                foreach ($activityRules as $activityRule) {
                    $activityParams = $activityRule->getActivityParameters();

                    $nbRules = is_null($activityParams->getRules()) ?
                        0 :
                        count($activityParams->getRules());

                    if (!is_null($user) && $nbRules > 0) {
                        $resources = $this->ruleValidator->validate(
                            $activityParams,
                            $user
                        );

                        if(0 < $resources['validRules']
                            && $resources['validRules'] >= $nbRules) {

                            $this->activityManager
                                ->updateEvaluation($user, $activityParams, $log);
                        }
                    }
                }
            }
        }
    }
}
