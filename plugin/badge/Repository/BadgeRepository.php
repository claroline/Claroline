<?php

namespace Icap\BadgeBundle\Repository;

use Icap\BadgeBundle\Entity\Badge;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class BadgeRepository extends EntityRepository
{
    /**
     * @param User $user
     * @param bool $executeQuery
     *
     * @return Query|array
     */
    public function findByUser(User $user, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT b, ub, bt
                FROM IcapBadgeBundle:Badge b
                JOIN b.userBadges ub
                JOIN b.translations bt
                WHERE ub.user = :userId'
            )
            ->setParameter('userId', $user->getId());

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * @param null|string $locale
     * @param bool        $executeQuery
     *
     * @return QueryBuilder|array
     */
    public function findOrderedByName($locale = null, $executeQuery = true)
    {
        $queryBuilder = $this->createQueryBuilder('badge')
            ->join('badge.translations', 'bt')
            ->where('bt.locale = :locale')
            ->orderBy('bt.name', 'ASC')
            ->setParameter('locale', $locale);

        return $executeQuery ? $queryBuilder->getQuery()->getResult() : $queryBuilder;
    }

    /**
     * @param string $slug
     * @param bool   $executeQuery
     *
     * @return array
     */
    public function findBySlug($slug, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT b
                FROM IcapBadgeBundle:Badge b
                JOIN b.translations t
                WHERE t.slug = :slug
                ORDER BY t.name ASC'
            )
            ->setParameter('slug', $slug);

        return $executeQuery ? $query->getSingleResult() : $query;
    }

    /**
     * @param string $name
     * @param string $locale
     * @param bool   $executeQuery
     *
     * @return Query|array
     */
    public function findByNameAndLocale($name, $locale, $executeQuery = true)
    {
        $name = strtoupper($name);
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT b, t
                FROM IcapBadgeBundle:Badge b
                JOIN b.translations t
                WHERE UPPER(t.name) LIKE :name
                AND t.locale = :locale
                ORDER BY t.name ASC'
            )
            ->setParameter('name', "%{$name}%")
            ->setParameter('locale', $locale);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * @param $name
     * @param $locale
     * @param $userId
     * @param bool|true $executeQuery
     *
     * @return array|Query
     */
    public function findByNameLocaleAndUserId($name, $locale, $userId, $executeQuery = true)
    {
        $name = strtoupper($name);
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT b, t
                FROM IcapBadgeBundle:Badge b
                JOIN b.translations t
                WHERE UPPER(t.name) LIKE :name
                AND t.locale = :locale
                AND b.workspace IS NULL
                OR b.workspace IN (SELECT w FROM ClarolineCoreBundle:Workspace\Workspace w
                    JOIN w.roles r
                    JOIN r.users u
                    WHERE u.id = :userId)
                ORDER BY t.name ASC'
            )
            ->setParameter('userId', $userId)
            ->setParameter('name', "%{$name}%")
            ->setParameter('locale', $locale);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * @param string $name
     *
     * @return Badge
     */
    public function findOneByName($name)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT b, t
                FROM IcapBadgeBundle:Badge b
                JOIN b.translations t
                WHERE t.name = :name'
            )
            ->setParameter('name', $name);

        return $query->getSingleResult();
    }

    /**
     * @param string $search
     * @param string $data
     *
     * @return array
     */
    public function findByNameForAjax($search, $data)
    {
        $resultArray = array();
        $locale = $data['locale'];
        $userId = $data['userId'];

        /** @var Badge[] $badges */
        $badges = $this->findByNameLocaleAndUserId($search, $locale, $userId);

        foreach ($badges as $badge) {
            $resultArray[] = array(
                'id' => $badge->getId(),
                'text' => $badge->getName($locale),
                'icon' => '<img src="/'.$badge->getWebPath().'" style="max-height:20px; max-width:20px;"/>',
            );
        }

        return $resultArray;
    }

    /**
     * @param array $params
     *
     * @return ArrayCollection
     */
    public function extract($params)
    {
        $search = $params['search'];
        if ($search !== null) {
            $query = $this->findByNameAndLocale($search, $params['extra']['locale'], false);

            return $query
                ->setFirstResult(0)
                ->setMaxResults(10)
                ->getResult();
        }

        return array();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param bool                                             $executeQuery
     *
     * @return Query|array
     */
    public function findByWorkspace(Workspace $workspace, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT b, ub, bt
                FROM IcapBadgeBundle:Badge b
                LEFT JOIN b.userBadges ub
                JOIN b.translations bt
                WHERE b.workspace = :workspaceId'
            )
            ->setParameter('workspaceId', $workspace->getId());

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * @param Workspace $workspace
     *
     * @return int
     */
    public function countByWorkspace(Workspace $workspace)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT(b.id)
                FROM IcapBadgeBundle:Badge b
                WHERE b.workspace = :workspaceId'
            )
            ->setParameter('workspaceId', $workspace->getId());

        return $query->getSingleScalarResult();
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $rootAlias
     * @param string       $locale
     *
     * @return QueryBuilder|array
     */
    public function orderByName(QueryBuilder $queryBuilder, $rootAlias, $locale)
    {
        $queryBuilder
            ->join(sprintf('%s.translations', $rootAlias), 'bt')
            ->andWhere('bt.locale = :locale')
            ->orderBy('bt.name', 'ASC')
            ->setParameter('locale', $locale);

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder   $queryBuilder
     * @param string         $rootAlias
     * @param Workspace|null $workspace
     *
     * @return QueryBuilder
     */
    public function filterByWorkspace(QueryBuilder $queryBuilder, $rootAlias, $workspace)
    {
        if (null === $workspace) {
            $queryBuilder->andWhere(sprintf('%s.workspace IS NULL', $rootAlias));
        } else {
            $queryBuilder
                ->andWhere(sprintf('%s.workspace = :workspace', $rootAlias))
                ->setParameter('workspace', $workspace);
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $rootAlias
     * @param array        $blacklist    Badge id we don't want to be retrieve
     *
     * @return QueryBuilder
     */
    public function filterByBlacklist(QueryBuilder $queryBuilder, $rootAlias, $blacklist)
    {
        if (0 < count($blacklist)) {
            $blacklistedBadgeIds = array();

            foreach ($blacklist as $blacklistedId) {
                $blacklistedBadgeIds[] = $blacklistedId;
            }

            $queryBuilder
                ->andWhere(sprintf('%s.id NOT IN (:badgeIds)', $rootAlias))
                ->setParameter('badgeIds', implode(',', $blacklistedBadgeIds));
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $rootAlias
     * @param User         $user
     *
     * @return QueryBuilder
     */
    public function filterByUser(QueryBuilder $queryBuilder, $rootAlias, User $user)
    {
        $queryBuilder
            ->join('badge.userBadges', 'ub')
            ->andWhere('ub.user = :user')
            ->setParameter('user', $user);

        return $queryBuilder;
    }
}
