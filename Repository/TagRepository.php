<?php

namespace Icap\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Icap\BlogBundle\Entity\Blog;

class TagRepository extends EntityRepository
{
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
                FROM IcapBlogBundle:Tag t
                JOIN t.posts p
                WHERE p.blog = :blogId
            ')
            ->setParameter('blogId', $blog->getId())
        ;

        return $executeQuery ? $query->getResult(): $query;
    }
}
