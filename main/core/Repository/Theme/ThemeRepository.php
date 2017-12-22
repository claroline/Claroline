<?php

namespace Claroline\CoreBundle\Repository\Theme;

use Claroline\CoreBundle\Entity\Theme\Theme;
use Doctrine\ORM\EntityRepository;

class ThemeRepository extends EntityRepository
{
    /**
     * Returns the themes corresponding to an array of UUIDs.
     *
     * @param array $uuids
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
