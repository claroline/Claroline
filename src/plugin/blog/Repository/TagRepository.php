<?php

namespace Icap\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Entity\Statusable;

class TagRepository extends EntityRepository
{
    /**
     * @param bool     $executeQuery
     * @param int|null $max
     *
     * @return array|\Doctrine\ORM\AbstractQuery
     */
    public function findByBlog(Blog $blog, $executeQuery = true, $max = null)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT t, COUNT(t.id) AS frequency, COUNT(p.id) as countPosts
                FROM IcapBlogBundle:Tag t
                JOIN t.posts p
                WHERE p.blog = :blogId
                AND p.status = :postStatus
                AND p.publicationDate IS NOT NULL
                GROUP BY t.id
                ORDER BY frequency DESC
            ')
            ->setParameter('blogId', $blog->getId())
            ->setParameter('postStatus', Statusable::STATUS_PUBLISHED)
        ;

        if (null !== $max) {
            $query->setMaxResults($max);
        }

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * @return array|\Doctrine\ORM\AbstractQuery
     */
    public function findByPost(Post $post)
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.posts', 'p')
            ->where('p.id = :postId')
            ->setParameter('postId', $post->getId())
            ->getQuery()->getResult();
    }
}
