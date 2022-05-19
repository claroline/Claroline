<?php

namespace Icap\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Post;

class PostRepository extends EntityRepository
{
    public function findAuthorsByBlog(Blog $blog)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT DISTINCT a.id
                FROM Icap\BlogBundle\Entity\Post p
                JOIN p.creator a
                WHERE p.blog = :blogId
            ')
            ->setParameter('blogId', $blog->getId())
        ;

        return $query->getResult();
    }

    public function findArchiveDataByBlog(Blog $blog)
    {
        $rsm = new ResultSetMapping();
        $rsm
        ->addScalarResult('c', 'count')
        ->addScalarResult('y', 'year')
        ->addScalarResult('m', 'month');

        $query = $this->getEntityManager()
            ->createNativeQuery('
                SELECT YEAR(p.publication_date) y, MONTH(p.publication_date) m, count(p.id) c
                FROM icap__blog_post p
                WHERE p.blog_id = :blog
                AND p.publication_date <= :currentDate
                AND p.status = :status
                GROUP BY y, m
                ORDER BY y, m DESC
            ', $rsm)
            ->setParameter('blog', $blog->getId())
            ->setParameter('currentDate', new \DateTime())
            ->setParameter('status', POST::STATUS_PUBLISHED)
        ;

        return $query->getResult();
    }
}
