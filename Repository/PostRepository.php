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

    /**
     * @param Blog   $blog
     * @param string $search
     * @param bool   $executeQuery
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function searchByBlog(Blog $blog, $search, $executeQuery = true)
    {
        $dql = 'SELECT p
                FROM ICAPBlogBundle:Post p
                WHERE p.blog = :blogId
        ';
        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('blogId', $blog->getId())
        ;

        $query = $this->createQueryBuilder('post')
            ->andWhere('post.blog = :blogId')
            ->setParameter('blogId', $blog->getId())
            ->orderBy('post.publicationDate', 'ASC')
        ;

        $searchCombinations = $this->getSearchCombinations($search);
        foreach($searchCombinations as $key => $searchParameter)
        {
            if(strlen($searchParameter) > 3) {
                $query
                    ->orWhere('post.title LIKE :search' . $key)
                    ->orWhere('post.content LIKE :search' . $key)
                    ->setParameter('search' . $key, '%' . $searchParameter . '%')
                ;
            }
        }

//        echo "<pre>";
//        var_dump(preg_replace(array('/(FROM)/', '/(WHERE)/', '/(AND)/', '/(OR )/', '/(ORDER BY)/'), PHP_EOL . '\1', $query->getQuery()->getSQL()));
//        echo "</pre>" . PHP_EOL;
//        die("FFFFFUUUUUCCCCCKKKKK" . PHP_EOL);

        return $executeQuery ? $query->getResult(): $query;
    }

    protected function getSearchCombinations($sentence)
    {
        $combinations = array($sentence);

        $words      = explode(' ', $sentence);
        $countWords = count($words);

        for($i = 1; $i < $countWords; $i++) {
            $combinations[] = implode(' ', array_slice($words, 0, $countWords - $i));
        }

        return $combinations;
    }
}
