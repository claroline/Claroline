<?php

namespace Claroline\LogBundle\Repository;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Doctrine\ORM\EntityRepository;

class FunctionalLogRepository extends EntityRepository
{
    public function findActionsForPeriod(string $objectClass, string $objectId, Organization $organization, ?string $actionType = null): array
    {
        $now = new \DateTime();
        $start = $now->sub(new \DateInterval('P52W'));

        $results = $this->getEntityManager()
            ->createQuery('
                SELECT YEAR(l.date) AS y, MONTH(l.date) AS m, DAY(l.date) AS d, COUNT(l) AS count
                FROM Claroline\LogBundle\Entity\FunctionalLog AS l
                LEFT JOIN l.doer AS u
                LEFT JOIN u.userOrganizationReferences AS uo
                WHERE uo.organization = :organization
                  AND l.objectClass = :objectClass
                  AND l.objectId = :objectId
                  AND l.date >= :start 
                GROUP BY y, m, d
            ')
            ->setParameter('objectClass', $objectClass)
            ->setParameter('objectId', $objectId)
            ->setParameter('organization', $organization)
            ->setParameter('start', $start)
            ->getArrayResult();

        $activity = [];
        foreach ($results as $result) {
            $activity[$result['y'].'-'.$result['m'].'-'.$result['d']] = (int) $result['count'];
        }

        return $activity;
    }
}
