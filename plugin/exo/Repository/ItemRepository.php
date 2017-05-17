<?php

namespace UJM\ExoBundle\Repository;

use Claroline\CoreBundle\Entity\User;
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
     * Search questions.
     *
     * @todo add date filters
     * @todo add user filters
     * @todo order query
     *
     * @param User      $user
     * @param \stdClass $filters
     * @param array     $orderBy
     * @param int       $number  - the number of results to get
     * @param int       $page    - the page to start (db offset is found with $number * $page)
     *
     * @return array
     */
    public function search(User $user, \stdClass $filters = null, array $orderBy = [], $number = -1, $page = 0)
    {
        $qb = $this->createQueryBuilder('q');

        if (empty($filters) || empty($filters->self_only)) {
            // Includes shared questions
            if (!empty($filters) && !empty($filters->creators)) {
                // Search by creators
            } else {
                // Get all questions of the current user
                $qb->leftJoin('UJM\ExoBundle\Entity\Item\Shared', 's', Join::WITH, 'q = s.question');
                $qb->where('(q.creator = :user OR s.user = :user)');
                $qb->setParameter('user', $user);
            }
        } else {
            // Only Get questions created by the User
            $qb->where('q.creator = :user');
            $qb->setParameter('user', $user);
        }

        // Type
        if (!empty($filters) && !empty($filters->types)) {
            $qb
                ->andWhere('q.mimeType IN (:types)')
                ->setParameter('types', $filters->types);
        }

        // in any case exclude every mimeType that does not begin with [application] from results
        $qb
            ->andWhere('q.mimeType LIKE :includedTypesPrefix')
            ->setParameter('includedTypesPrefix', 'application%');

        // Title / Content
        if (!empty($filters) && !empty($filters->title)) {
            $qb
                ->andWhere('(q.content LIKE :text OR q.title LIKE :text)')
                ->setParameter('text', '%'.addcslashes($filters->title, '%_').'%');
        }

        // Categories
        if (!empty($filters) && !empty($filters->categories)) {
            $qb->andWhere('q.category IN (:categories)');
            $qb->setParameter('categories', $filters->categories);
        }

        // Exercises
        if (!empty($filters) && !empty($filters->exercises)) {
            $qb
                ->join('q.stepQuestions', 'sq')
                ->join('sq.step', 's')
                ->join('s.exercise', 'e')
                ->andWhere('e.uuid IN (:exercises)');

            $qb->setParameter('exercises', $filters->exercises);
        }

        // Model
        if (!empty($filters) && !empty($filters->model_only)) {
            $qb->andWhere('q.model = true');
        }

        if (-1 !== $number) {
            // We don't want to load the full list
            $qb
                ->setFirstResult($page * $number)
                ->setMaxResults($number);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

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
