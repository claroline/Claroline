<?php

namespace ICAP\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ICAP\BlogBundle\Entity\Blog;

class TagRepository extends EntityRepository
{
    public function extract($params)
    {
        $q = $this->extractQuery($params);

        return is_null($q) ? array() : $q->getResult();
    }

    /**
     * @param Blog $blog
     * @param bool $executeQuery
     *
     * @return array|\Doctrine\ORM\AbstractQuery
     */
    public function findByBlog(Blog $blog, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT t
                FROM ICAPBlogBundle:Tag t
                JOIN t.posts p
                WHERE p.blog = :blogId
            ')
            ->setParameter('blogId', $blog->getId())
        ;

        return $executeQuery ? $query->getResult(): $query;
    }
}
