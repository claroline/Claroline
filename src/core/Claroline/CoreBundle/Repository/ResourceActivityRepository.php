<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ResourceActivityRepository extends EntityRepository
{
    public function getResourcesActivityForActivity($activity)
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


