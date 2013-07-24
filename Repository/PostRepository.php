<?php

namespace ICAP\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ICAP\BlogBundle\Entity\Blog;

class PostRepository extends EntityRepository
{
    /**
     * @param Blog $blog
     * @param bool $executeQuery
     *
     * @return array|\Doctrine\ORM\AbstractQuery
     */
    public function findAuthorsByBlog(Blog $blog, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT DISTINCT a.id, a.username, a.firstName, a.lastName
                FROM ICAPBlogBundle:Post p
                JOIN p.author a
                WHERE p.blog = :blogId
            ')
            ->setParameter('blogId', $blog->getId())
        ;

        return $executeQuery ? $query->getResult(): $query;
    }
}
