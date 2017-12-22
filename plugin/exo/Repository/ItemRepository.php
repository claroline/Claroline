<?php

namespace UJM\ExoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Item;

/**
 * ItemRepository.
 */
class ItemRepository extends EntityRepository
{
    /**
     * Returns all the questions linked to a given exercise.
     *
     * @deprecated this is not used
     *
     * @param Exercise $exercise
     *
     * @return Item[]
     */
    public function findByExercise(Exercise $exercise)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT q
                FROM UJM\ExoBundle\Entity\Item\Item AS q
                JOIN UJM\ExoBundle\Entity\StepItem AS sq WITH sq.question = q
                JOIN UJM\ExoBundle\Entity\Step AS s WITH sq.step = s AND s.exercise = :exercise
            ')
            ->setParameter('exercise', $exercise)
            ->getResult();
    }

    /**
     * Returns the questions corresponding to an array of UUIDs.
     *
     * @param array $uuids
     *
     * @return Item[]
     */
    public function findByUuids(array $uuids)
    {
        return $this->createQueryBuilder('q')
            ->where('q.uuid IN (:uuids)')
            ->setParameter('uuids', $uuids)
            ->getQuery()
            ->getResult();
    }
}
