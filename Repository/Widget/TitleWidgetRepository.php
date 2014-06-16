<?php

namespace Icap\PortfolioBundle\Repository\Widget;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class TitleWidgetRepository extends EntityRepository
{
    /**
     * @param User $user
     * @param bool $executeQuery
     *
     * @return array|\Doctrine\ORM\Query
     */
    public function findAllForUser(User $user, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('tw')
            ->select(array('tw', 'tw.id', 'Max(wn.updatedAt) as updatedAt', 'tw.title', 'tw.slug', 'p.id as portfolioId', 'p.visibility as visibility'))
            ->join('tw.widgetNode', 'wn')
            ->join('wn.widgetType', 'wt')
            ->join('wn.portfolio', 'p')
            ->andWhere('wt.name = :type')
            ->andWhere('p.user = :user')
            ->setParameter('type', 'title')
            ->setParameter('user', $user)
        ;

        return $executeQuery ? $query->getQuery()->getResult(): $query->getQuery();
    }

    /**
     * @param string $slug
     * @param bool   $executeQuery
     *
     * @return array|\Doctrine\ORM\Query
     */
    public function findOneBySlug($slug, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('tw')
            ->select(array('tw', 'p'))
            ->join('tw.portfolio', 'p')
            ->andWhere('tw.slug = :slug')
            ->setParameter('slug', $slug)
        ;

        return $executeQuery ? $query->getQuery()->getSingleResult(): $query->getQuery();
    }
}