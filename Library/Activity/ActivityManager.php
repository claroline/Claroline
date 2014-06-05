<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Activity;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\Activity;

/**
 * @Service()
 */
class ActivityManager
{
    private $persistence;

    /**
     * @InjectParams({
     *     "persistence" = @Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $persistence)
    {
        $this->persistence = $persistence;
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
        $this->manager->persist($activity);
        $this->manager->flush();
    }

    /**
     * Delete an activity
     */
    public function deleteActivty($activity)
    {
        $this->manager->remove($activity);
        $this->manager->flush();
    }

    /**
     * Link a resource to an activity
     */
    public function addResource($resourceActivity)
    {
        $this->manager->persist($resourceActivity);
        $this->manager->flush();
    }

    /**
     * Edit a resource link in an activity
     */
    public function editResource($resourceActivity)
    {
        $this->manager->persist($resourceActivity);
        $this->manager->flush();
    }

    /**
     * delete a resource from an activity
     */
    public function deleteResource($resourceActivity)
    {
        $this->manager->persist($resourceActivity);
        $this->manager->flush();
    }
}
