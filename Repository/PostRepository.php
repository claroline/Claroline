<?php

namespace ICAP\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ICAP\BlogBundle\Entity\Blog;
use ICAP\BlogBundle\Entity\Statusable;
use ICAP\BlogBundle\Exception\TooMuchResultException;

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

    /**
     * @param Blog   $blog
     * @param string $search
     * @param bool   $executeQuery
     *
     * @throws TooMuchResultException
     * @return array|\Doctrine\ORM\QueryBuilder
     */
    public function searchByBlog(Blog $blog, $search, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('post')
            ->andWhere('post.blog = :blogId')
            ->setParameter('blogId', $blog->getId())
            ->orderBy('post.publicationDate', 'ASC')
        ;

        $forbiddenWords = array('le', 'la', 'là', 'les', 'des', 'de', 'du', 'en', 'et', 'à', 'dans', 'me', 'mes', 'mon', 'ma',
            'te', 'tes', 'ton', 'ta', 'se', 'ses', 'son', 'sa', 'ça', 'un', 'une', 'ou', 'donc', 'il', 'elle',
            'on', 'nous', 'vous', 'ils', 'elles', 'eux', 'mien', 'sien', 'pour', 'que', 'qui', 'quand', 'quoi', 'quel',
            'quels', 'quelle', 'quelles', 'par', 'tout', 'tous', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n',
            'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
        );

        $searchParameters = explode(' ', trim($search));
        $titleCondition   = '';
        $contentCondition = '';
        $hasWords         = false;
        foreach($searchParameters as $key => $searchParameter)
        {
            if(false === in_array($searchParameter, $forbiddenWords)) {
                $hasWords = true;
                $titleCondition .= "post.title LIKE :titleSearch$key";
                $contentCondition .= "post.content LIKE :contentSearch$key";
                if($key < count($searchParameters) - 1) {
                    $titleCondition .= " AND ";
                    $contentCondition .= " AND ";
                }

                $query->setParameter('titleSearch' . $key, '%' . $searchParameter . '%');
                $query->setParameter('contentSearch' . $key, '%' . $searchParameter . '%');
            }
        }

        if($hasWords) {
            $query->andWhere('(' . $titleCondition . ') OR (' . $contentCondition . ')');
        }
        else {
            throw new TooMuchResultException();
        }

        return $executeQuery ? $query->getResult(): $query;
    }

    /**
     * @param Blog $blog
     * @param int  $startDate
     * @param int  $endDate
     * @param bool $executeQuery
     *
     * @return array|\Doctrine\ORM\AbstractQuery
     */
    public function findPublishedByBlogAndDates(Blog $blog, $startDate, $endDate, $executeQuery = true)
    {
        $startDateTime = new \DateTime();
        $startDateTime->setTimestamp($startDate);

        $endDateTime = new \DateTime();
        $endDateTime->setTimestamp($endDate);

        $query = $this->createQueryBuilder('post')
            ->andWhere('post.blog = :blogId')
            ->andWhere('post.status = :postStatus')
            ->andWhere('post.publicationDate IS NOT NULL')
            ->andWhere('post.publicationDate BETWEEN :startDate AND :endDate')
            ->setParameter('blogId', $blog->getId())
            ->setParameter('postStatus', Statusable::STATUS_PUBLISHED)
            ->setParameter('startDate', $startDateTime)
            ->setParameter('endDate', $endDateTime)
            ->getQuery()
        ;

        return $executeQuery ? $query->getResult(): $query;
    }


    /**
     * @param Blog $blog
     * @param bool $executeQuery
     *
     * @return array|\Doctrine\ORM\AbstractQuery
     */
    public function findArchiveDatasByBlog(Blog $blog, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT SUBSTRING(p.publicationDate, 1, 4) as year, SUBSTRING(p.publicationDate, 6, 2) as month, COUNT(p) as number
                FROM ICAPBlogBundle:Post p
                WHERE p.blog = :blogId
                GROUP BY year, month
                ORDER BY year DESC
            ')
            ->setParameter('blogId', $blog->getId())
        ;

        return $executeQuery ? $query->getResult(): $query;
    }
}
