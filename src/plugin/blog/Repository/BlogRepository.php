<?php

namespace Icap\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;

class BlogRepository extends EntityRepository
{
    /**
     * @param array $resourceNodeIds
     * @param bool  $executeQuery
     *
     * @return Blog[]|\Doctrine\ORM\Query
     */
    public function findByResourceNodeIds(array $resourceNodeIds, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('blog')
            ->select(array('blog', 'bo', 'post'))
            ->join('blog.options', 'bo')
            ->leftJoin('blog.posts', 'post')
            ->where('blog.resourceNode IN (:resourceNodeIds)')
            ->setParameter('resourceNodeIds', $resourceNodeIds)
            ->getQuery()
        ;

        return $executeQuery ? $query->getResult() : $query;
    }
}
