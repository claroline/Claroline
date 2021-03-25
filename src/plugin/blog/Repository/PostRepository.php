<?php

namespace Icap\BlogBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Entity\Statusable;
use Icap\BlogBundle\Entity\Tag;
use Icap\BlogBundle\Exception\TooMuchResultException;

class PostRepository extends EntityRepository
{
    public function getByDateDesc(Blog $blog, $executeQuery = true, $forAdmin = false)
    {
        $query = $this->createQueryBuilder('post')
            ->select(['post'])
            ->andWhere('post.blog = :blogId')
            ->setParameter('blogId', $blog->getId())
            ->orderBy('post.publicationDate', 'DESC');

        // User requesting post list is not an admin of the blog, post list displays only published posts
        if (!$forAdmin) {
            $query
                ->andWhere('post.status = :postStatus')
                ->setParameter('postStatus', true);
        }

        return $executeQuery ? $query->getQuery()->getResult() : $query;
    }

    /**
     * @param bool $executeQuery
     *
     * @return array|\Doctrine\ORM\AbstractQuery
     */
    public function findAuthorsByBlog(Blog $blog, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT DISTINCT a.id
                FROM IcapBlogBundle:Post p
                JOIN p.author a
                WHERE p.blog = :blogId
            ')
            ->setParameter('blogId', $blog->getId())
        ;

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * @param string $search
     * @param bool   $executeQuery
     *
     * @throws TooMuchResultException
     *
     * @return array|\Doctrine\ORM\QueryBuilder
     */
    public function searchByBlog(Blog $blog, $search, $executeQuery = true, $isAdmin = true)
    {
        $query = $this->createQueryBuilder('post')
            ->andWhere('post.blog = :blogId')
            ->setParameter('blogId', $blog->getId())
            ->orderBy('post.publicationDate', 'ASC')
        ;

        if (!$isAdmin) {
            $query
                ->andWhere('post.publicationDate IS NOT NULL')
                ->andWhere('post.status = :publishedStatus')
                ->setParameter('publishedStatus', Statusable::STATUS_PUBLISHED)
            ;
        }

        $forbiddenWords = ['le', 'la', 'là', 'les', 'des', 'de', 'du', 'en', 'et', 'à', 'dans', 'me', 'mes', 'mon', 'ma',
            'te', 'tes', 'ton', 'ta', 'se', 'ses', 'son', 'sa', 'ça', 'un', 'une', 'ou', 'donc', 'il', 'elle',
            'on', 'nous', 'vous', 'ils', 'elles', 'eux', 'mien', 'sien', 'pour', 'que', 'qui', 'quand', 'quoi', 'quel',
            'quels', 'quelle', 'quelles', 'par', 'tout', 'tous', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n',
            'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        ];

        $searchParameters = explode(' ', trim($search));
        $titleCondition = '';
        $contentCondition = '';
        $hasWords = false;
        foreach ($searchParameters as $key => $searchParameter) {
            if (false === in_array($searchParameter, $forbiddenWords)) {
                $hasWords = true;
                $titleCondition .= "post.title LIKE :titleSearch$key";
                $contentCondition .= "post.content LIKE :contentSearch$key";
                if ($key < count($searchParameters) - 1) {
                    $titleCondition .= ' AND ';
                    $contentCondition .= ' AND ';
                }

                $query->setParameter('titleSearch'.$key, '%'.$searchParameter.'%');
                $query->setParameter('contentSearch'.$key, '%'.$searchParameter.'%');
            }
        }

        if ($hasWords) {
            $query->andWhere('('.$titleCondition.') OR ('.$contentCondition.')');
        } else {
            throw new TooMuchResultException();
        }

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findAllPublicByBlog(Blog $blog)
    {
        $qb = $this->createQueryBuilder('post')
            ->andWhere('post.blog = :blogId')
            ->setParameter('blogId', $blog->getId())
            ->orderBy('post.publicationDate', 'ASC')
            ->andWhere('post.publicationDate IS NOT NULL')
            ->andWhere('post.status = :publishedStatus')
            ->setParameter('publishedStatus', Statusable::STATUS_PUBLISHED)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int  $startDate
     * @param int  $endDate
     * @param bool $executeQuery
     *
     * @return array|\Doctrine\ORM\AbstractQuery
     */
    public function findCalendarDatas(Blog $blog, $startDate, $endDate, $executeQuery = true)
    {
        $startDateTime = new \DateTime($startDate);
        $currentTimestamp = time();
        $endDateTime = new \DateTime($endDate);

        if ($currentTimestamp < $endDate) {
            $endDateTime->setTimestamp($currentTimestamp);
        }

        $query = $this->createQueryBuilder('post')
            ->select(['post'])
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

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * @param bool $executeQuery
     *
     * @return Post[]|\Doctrine\ORM\AbstractQuery
     */
    public function findRssDatas(Blog $blog, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('post')
            ->select(['post'])
            ->andWhere('post.blog = :blogId')
            ->andWhere('post.status = :postStatus')
            ->andWhere('post.publicationDate IS NOT NULL')
            ->setParameter('blogId', $blog->getId())
            ->setParameter('postStatus', Statusable::STATUS_PUBLISHED)
            ->getQuery()
        ;

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * @param bool $executeQuery
     *
     * @return array|\Doctrine\ORM\AbstractQuery
     */
    public function findArchiveDatasByBlog(Blog $blog, $executeQuery = true)
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

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * @return QueryBuilder
     */
    public function filterByPublishPost(QueryBuilder $query)
    {
        return $query
            ->andWhere('post.publicationDate IS NOT NULL')
            ->andWhere('post.status = :publishedStatus')
            ->andWhere('post.publicationDate <= :currentDate')
            ->setParameter('publishedStatus', Statusable::STATUS_PUBLISHED)
            ->setParameter('currentDate', new \DateTime());
    }

    /**
     * @param array $criterias
     *
     * @return QueryBuilder
     *
     * @throws \InvalidArgumentException
     */
    public function createCriteriaQueryBuilder($criterias, QueryBuilder $query)
    {
        $tag = $criterias['tag'];
        $author = $criterias['author'];
        $date = $criterias['date'];
        $blogId = $criterias['blogId'];

        if (null !== $tag) {
            $query
                ->join('post.tags', 'pt')
                ->andWhere('pt.id = :tagId')
                ->setParameter('tagId', $tag->getId())
            ;
        } elseif (null !== $author) {
            $query
                ->andWhere('post.author = :authorId')
                ->setParameter('authorId', $author->getId())
            ;
        } elseif (null !== $date) {
            $dates = explode('-', $date);
            $startDate = new \DateTime();
            $endDate = new \DateTime();

            $countDateParts = count($dates);
            if (2 === $countDateParts) {
                $startDate
                    ->setDate($dates[1], $dates[0], 1)
                    ->setTime(0, 0);
                $endDate->setTimestamp(strtotime('+1 month', $startDate->getTimestamp()));
            } elseif (2 < $countDateParts) {
                $startDate
                    ->setDate($dates[2], $dates[1], $dates[0])
                    ->setTime(0, 0);
                $endDate->setTimestamp(strtotime('+1 day', $startDate->getTimestamp()));
            } else {
                throw new \InvalidArgumentException('Invalid format for date filter argument');
            }

            $query
                ->andWhere('post.publicationDate >= :startDate')
                ->andWhere('post.publicationDate < :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        $query
            ->setParameter('blogId', $blogId)
            ->orderBy('post.publicationDate', 'DESC')
        ;

        return $query;
    }

    public function getByTag(Blog $blog, Tag $tag, $filterByPublishPost, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('post')
            ->select(['post', 'author'])
            ->join('post.author', 'author')
            ->andWhere('post.blog = :blogId');

        if ($filterByPublishPost) {
            $query = $this->filterByPublishPost($query);
        }

        $criterias = [
            'tag' => $tag,
            'author' => null,
            'date' => null,
            'blogId' => $blog->getId(),
        ];

        $query = $this->createCriteriaQueryBuilder($criterias, $query);

        return $executeQuery ? $query->getQuery()->getResult() : $query;
    }

    public function getByAuthor(Blog $blog, User $author, $filterByPublishPost, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('post')
            ->select(['post', 'author'])
            ->join('post.author', 'author')
            ->andWhere('post.blog = :blogId');

        if ($filterByPublishPost) {
            $query = $this->filterByPublishPost($query);
        }

        $criterias = [
            'tag' => null,
            'author' => $author,
            'date' => null,
            'blogId' => $blog->getId(),
        ];

        $query = $this->createCriteriaQueryBuilder($criterias, $query);

        return $executeQuery ? $query->getQuery()->getResult() : $query;
    }

    public function getByDate(Blog $blog, $date, $filterByPublishPost, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('post')
            ->select(['post', 'author'])
            ->join('post.author', 'author')
            ->andWhere('post.blog = :blogId');

        if ($filterByPublishPost) {
            $query = $this->filterByPublishPost($query);
        }

        $criterias = [
            'tag' => null,
            'author' => null,
            'date' => $date,
            'blogId' => $blog->getId(),
        ];

        $query = $this->createCriteriaQueryBuilder($criterias, $query);

        return $executeQuery ? $query->getQuery()->getResult() : $query;
    }
}
