<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Resource\Activity;

class ResourceActivityRepository extends EntityRepository
{
    /**
     * Returns the resources that are steps of a given activity.
     *
     * @param Activity $activity
     *
     * @return array[Activity]
     */
    public function findResourceActivities(Activity $activity)
    {
        $dql = "
            SELECT ra, r FROM Claroline\CoreBundle\Entity\Resource\ResourceActivity ra
            LEFT JOIN ra.resourceNode r
            LEFT JOIN ra.activity a
            WHERE a.id = {$activity->getId()}
            ORDER BY ra.sequenceOrder
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}
