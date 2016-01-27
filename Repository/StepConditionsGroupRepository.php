<?php

namespace Innova\PathBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Repository\GroupRepository;

class StepConditionsGroupRepository extends GroupRepository
{
    /**
     * Get list of group for a given user
     *
     * @param $userId
     * @param bool $executeQuery
     * @param string $orderedBy
     * @param null $order
     * @return array|\Doctrine\ORM\Query
     */
    public function getAllForUser($userId, $executeQuery = true, $orderedBy = 'id', $order = null)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('g')
            ->from('Claroline\CoreBundle\Entity\Group', 'g')
            ->leftJoin('g.users', 'u')
            ->where('u.id = :id')
            ->setParameter('id', $userId);
        $query = $qb->getQuery();
        return $executeQuery ? $query->getResult() : $query;
    }
}