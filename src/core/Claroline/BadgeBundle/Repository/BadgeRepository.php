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
     * @param bool $executeQuery
     *
     * @return Query|array
     */
    public function findUsers(Badge $badge, $executeQuery = true)
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

        return $executeQuery ? $query->getResult(): $query;
    }

    /**
     * @param User $user
     *
     * @param bool $executeQuery
     *
     * @return Query|array
     */
    public function findByUser(User $user, $executeQuery = true)
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

        return $executeQuery ? $query->getResult(): $query;
    }

    /**
     * @param null|string $locale
     *
     * @param bool $executeQuery
     *
     * @return Query|array
     */
    public function findOrderedByName($locale = null, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT b, t
                FROM ClarolineBadgeBundle:Badge b
                JOIN b.translations t
                WHERE t.locale = :locale
                ORDER BY t.name ASC'
            )
            ->setParameter('locale', $locale)
        ;

        return $executeQuery ? $query->getResult(): $query;
    }

    /**
     * @param string $slug
     *
     * @param bool $executeQuery
     *
     * @return array
     */
    public function findBySlug($slug, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT b, t
                FROM ClarolineBadgeBundle:Badge b
                JOIN b.translations t
                WHERE t.slug = :slug
                ORDER BY t.name ASC'
            )
            ->setParameter('slug', $slug)
        ;

        return $executeQuery ? $query->getSingleResult(): $query;
    }

    /**
     * @param string $name
     * @param bool   $executeQuery
     *
     * @return Query|array
     */
    public function findByName($name, $executeQuery = true)
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

        return $executeQuery ? $query->getResult(): $query;
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
     * @param  array           $params
     * @return ArrayCollection
     */
    public function extract($params)
    {
        $search = $params['search'];
        if ($search !== null) {

            $query = $this->findByName($search, false);

            return $query
                ->setFirstResult(0)
                ->setMaxResults(10)
                ->getResult()
            ;
        }

        return array();
    }
}
