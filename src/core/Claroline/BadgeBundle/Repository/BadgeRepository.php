<?php

namespace Claroline\BadgeBundle\Repository;

use Claroline\BadgeBundle\Entity\Badge;
use Claroline\CoreBundle\Entity\User;
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
     * @param User $user
     *
     * @param bool $getQuery
     *
     * @return Query|array
     */
    public function findByUser(User $user, $getQuery = false)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT b, ub, bt
                FROM ClarolineBadgeBundle:Badge b
                JOIN b.userBadges ub
                JOIN b.translations bt
                WHERE ub.user = :userId
            ')
            ->setParameter('userId', $user->getId())
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
                FROM ClarolineBadgeBundle:Badge b
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
                FROM ClarolineBadgeBundle:Badge b
                JOIN b.translations t
                WHERE t.slug = :slug
                ORDER BY t.name ASC'
            )
            ->setParameter('slug', $slug)
            ->getSingleResult()
        ;
    }

    /**
     * @param string $name
     * @param bool   $getQuery
     *
     * @return array|\Doctrine\ORM\AbstractQuery
     */
    public function findByName($name, $getQuery = false)
    {
        $name  = strtoupper($name);
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT b, t
                FROM ClarolineBadgeBundle:Badge b
                JOIN b.translations t
                WHERE UPPER(t.name) LIKE :name
                ORDER BY t.name ASC'
            )
            ->setParameter('name', "%{$name}%")
        ;

        return ($getQuery) ? $query: $query->getResult();
    }

    /**
     * @param string $name
     *
     * @return Badge
     */
    public function findOneByName($name)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT b, t
                FROM ClarolineBadgeBundle:Badge b
                JOIN b.translations t
                WHERE t.name = :name'
            )
            ->setParameter('name', $name)
        ;

        return $query->getSingleResult();
    }

    /**
     * @param array $params
     * @return ArrayCollection
     */
    public function extract($params)
    {
        $search = $params['search'];
        if ($search !== null) {

            $query = $this->findByName($search, true);

            return $query
                ->setFirstResult(0)
                ->setMaxResults(10)
                ->getResult()
            ;
        }

        return array();
    }
}
