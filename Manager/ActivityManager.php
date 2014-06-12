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
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
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
    private $persistence;

    /**
     * @InjectParams({
     *     "persistence"        = @Inject("claroline.persistence.object_manager"),
     * })
     */
    public function __construct(ObjectManager $persistence)
    {
        $this->persistence = $persistence;
        $this->activityRuleRepo = $persistence->getRepository('ClarolineCoreBundle:Activity\ActivityRule');
        $this->evaluationRepo = $persistence->getRepository('ClarolineCoreBundle:Activity\Evaluation');
    }


    /**
     * Access to ActivityRuleRepository methods
     */
    public function getActivityRuleByActionAndResource(
        $action,
        ResourceNode $resourceNode,
        $executeQuery = true
    )
    {
        return $this->activityRuleRepo->findActivityRuleByActionAndResource(
            $action,
            $resourceNode->getId(),
            $executeQuery
        );
    }

    /**
     * Access to EvaluationRepository methods
     */
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

    /**
     * Edit an activity
     */
    public function editActivity($activity)
    {
        $this->persistence->persist($activity);
        $this->persistence->flush();

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
    public function addResource($activity, $resource)
    {
        if (!$activity->getParameters()->getSecondaryResources()->contains($resource)) {
            $activity->getParameters()->getSecondaryResources()->add($resource);
            $this->persistence->persist($activity);
            $this->persistence->flush();

            return true;
        }
    }

    /**
     * Remove a resource from an activity
     */
    public function removeResource($activity, $resource)
    {
        if ($activity->getParameters()->getSecondaryResources()->contains($resource)) {
            $activity->getParameters()->getSecondaryResources()->removeElement($resource);
            $this->persistence->persist($activity);
            $this->persistence->flush();

            return true;
        }
    }
}
