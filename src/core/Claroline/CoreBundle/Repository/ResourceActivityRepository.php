<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Resource\Activity;

class ResourceActivityRepository extends EntityRepository
{
    /**
     * Returns the actvity that were set in an activity.
     *
     * @param Activity $activity
     *
     * @return array
     */
    public function findResourceActivities(Activity $activity)
    {
        $dql = "SELECT ra, r FROM Claroline\CoreBundle\Entity\Resource\ResourceActivity ra
            LEFT JOIN ra.resource r
            LEFT JOIN ra.activity a
            WHERE a.id = {$activity->getId()}
            ORDER BY ra.sequenceOrder
            ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}


