<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Activity\ActivityParameters;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\ActivityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class ActivityRuleController extends Controller
{
    private $activityManager;

    /**
     * @DI\InjectParams({
     *     "activityManager" = @DI\Inject("claroline.manager.activity_manager")
     * })
     */
    public function __construct(ActivityManager $activityManager)
    {
        $this->activityManager = $activityManager;
    }

    /**
     * @EXT\Route(
     *     "activity/add/rule/{activityParamsId}/{action}/{occurrence}/{result}/{activeFrom}/{activeUntil}/{resourceNodeId}",
     *     name="claro_add_rule_to_activity",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "actvityParams",
     *      class="ClarolineCoreBundle:Activity\ActivityParameters",
     *      options={"id" = "activityParamsId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "resourceNode",
     *      class="ClarolineCoreBundle:Resource\ResourceNode",
     *      options={"id" = "resourceNodeId", "strictId" = true}
     * )
     *
     * Creates a rule and associates it to the activity
     *
     * @param ActivityParameters $activityParams
     * @param string $action
     * @param int $occurrence
     * @param string $result
     * @param datetime $activeFrom
     * @param datetime $activeUntil
     * @param ResourceNode $resourceNode
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addRuleToActivity(
        ActivityParameters $activityParams,
        $action,
        $occurrence,
        $result,
        $activeFrom,
        $activeUntil,
        ResourceNode $resourceNode = null
    )
    {
        $this->activityManager->createActivityRule(
            $activityParams,
            $action,
            $occurrence,
            $result,
            $activeFrom,
            $activeUntil,
            $resourceNode
        );
    }
}
