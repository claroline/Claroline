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
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

/**
 * @Service("claroline.manager.activity_manager")
 */
class ActivityManager
{
    private $activityRuleRepo;
    private $evaluationRepo;
    private $persistence;

    /**
     * Constructor.
     *
     * @InjectParams({
     *     "persistence" = @Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $persistence)
    {
        $this->persistence = $persistence;
        $this->activityRuleRepo = $persistence->getRepository('ClarolineCoreBundle:Activity\ActivityRule');
        $this->evaluationRepo = $persistence->getRepository('ClarolineCoreBundle:Activity\Evaluation');
    }

    /**
     * Create a new activity
     */
    public function createActivity()
    {
        $this->editActivity(new Activity());
    }

    /**
     * Edit an activity
     */
    public function editActivity($activity)
    {
        $this->persistence->persist($activity);
        $this->persistence->flush();
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
            $resourceNode->getId(),
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
}
