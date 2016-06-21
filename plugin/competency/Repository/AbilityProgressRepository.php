<?php

namespace HeVinci\CompetencyBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class AbilityProgressRepository extends EntityRepository
{
    /**
     * Returns progress entities for a given user which are related to
     * a set of abilities and have a particular status.
     *
     * @param User   $user
     * @param array  $abilities
     * @param string $status
     *
     * @return array
     */
    public function findByAbilitiesAndStatus(User $user, array $abilities, $status)
    {
        return $this->createQueryBuilder('ap')
            ->select('ap')
            ->where('ap.user = :user')
            ->andWhere('ap.ability IN (:abilities)')
            ->andWhere('ap.status = :status')
            ->orderBy('ap.id')
            ->setParameters([
                ':user' => $user,
                ':abilities' => $abilities,
                ':status' => $status,
            ])
            ->getQuery()
            ->getResult();
    }
}
