<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class BadgeRepository extends EntityRepository
{
    /**
     * @param null|string $locale
     *
     * @return array
     */
    public function findAllOrderedByName($locale = null)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT b, t
                FROM ClarolineCoreBundle:Badge\Badge b
                JOIN b.translations t
                WHERE t.locale = :locale
                ORDER BY t.name ASC'
            )
            ->setParameter('locale', $locale)
            ->getResult();
    }
}
