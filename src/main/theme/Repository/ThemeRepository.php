<?php

namespace Claroline\ThemeBundle\Repository;

use Claroline\ThemeBundle\Entity\Theme;
use Doctrine\ORM\EntityRepository;

class ThemeRepository extends EntityRepository
{
    /**
     * Returns the themes corresponding to an array of UUIDs.
     *
     * @return Theme[]
     */
    public function findByUuids(array $uuids)
    {
        return $this->createQueryBuilder('t')
            ->where('t.uuid IN (:uuids)')
            ->setParameter('uuids', $uuids)
            ->getQuery()
            ->getResult();
    }
}
