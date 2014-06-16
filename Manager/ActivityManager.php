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
    private $om;

    /**
     * @InjectParams({
     *     "om"        = @Inject("claroline.persistence.object_manager"),
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->activityRuleRepo = $om->getRepository('ClarolineCoreBundle:Activity\ActivityRule');
        $this->evaluationRepo = $om->getRepository('ClarolineCoreBundle:Activity\Evaluation');
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
}
