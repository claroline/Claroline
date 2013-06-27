<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class BadgeRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findAllOrderedByName()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT b FROM ClarolineCoreBundle:Badge\Badge b ORDER BY b.name ASC')
            ->getResult();
    }
}
