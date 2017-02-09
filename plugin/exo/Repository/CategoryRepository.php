<?php

namespace UJM\ExoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use UJM\ExoBundle\Entity\Item\Category;

/**
 * Category repository.
 */
class CategoryRepository extends EntityRepository
{
    /**
     * Counts the number of questions of a category.
     *
     * @param Category $category
     *
     * @return int
     */
    public function countQuestions(Category $category)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT COUNT(q) FROM UJM\ExoBundle\Entity\Item\Item q WHERE q.category = :category')
            ->setParameter('category', $category)
            ->getSingleScalarResult();
    }
}
