<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class BadgeRepository extends EntityRepository
{
    /**
     * @param Badge $badge
     *
     * @param bool  $getQuery
     *
     * @return Query|array
     */
    public function findUsers(Badge $badge, $getQuery = false)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT ub, u
                FROM ClarolineCoreBundle:User u
                JOIN u.userBadges ub
                WHERE ub.badge = :badgeId
                ORDER BY u.lastName ASC'
            )
            ->setParameter('badgeId', $badge->getId())
        ;

        return ($getQuery) ? $query: $query->getResult();
    }

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

    /**
     * @param string      $slug
     *
     * @return array
     */
    public function findBySlug($slug)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT b, t
                FROM ClarolineCoreBundle:Badge\Badge b
                JOIN b.translations t
                WHERE t.slug = :slug
                ORDER BY t.name ASC'
            )
            ->setParameter('slug', $slug)
            ->getSingleResult()
        ;
    }
}
