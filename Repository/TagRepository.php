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
    public function findByBlog(Blog $blog, $executeQuery = true, $max = null)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT t, COUNT(t.id) AS frequency
                FROM IcapBlogBundle:Tag t
                JOIN t.posts p
                WHERE p.blog = :blogId
                GROUP BY t.id
                ORDER BY frequency DESC
            ')
            ->setParameter('blogId', $blog->getId())
        ;

        if ($max != null) {
            $query->setMaxResults($max);
        }

        return $executeQuery ? $query->getResult(): $query;
    }
}
