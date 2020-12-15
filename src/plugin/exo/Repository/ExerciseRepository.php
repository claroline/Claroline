<?php

namespace UJM\ExoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Item;

/**
 * ExerciseRepository.
 */
class ExerciseRepository extends EntityRepository
{
    /**
     * Lists scores obtained to an exercise.
     *
     * @param Exercise $exercise
     *
     * @return array
     */
    public function findScores(Exercise $exercise)
    {
    }

    /**
     * Retrieves exercises using a question.
     *
     * @param Item $question
     *
     * @return array
     */
    public function findByQuestion(Item $question)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT e
                FROM UJM\ExoBundle\Entity\Exercise AS e
                JOIN UJM\ExoBundle\Entity\Step AS s WITH s.exercise = e
                JOIN UJM\ExoBundle\Entity\StepItem AS sq WITH sq.step = s AND sq.question = :question
            ')
            ->setParameter('question', $question)
            ->getResult();
    }

    public function invalidatePapers(Exercise $exercise)
    {
        return $this->getEntityManager()
            ->createQuery('
                UPDATE UJM\ExoBundle\Entity\Attempt\Paper AS p
                SET p.invalidated = true
                WHERE p.exercise = :exercise
                  AND p.invalidated = false
            ')
            ->setParameters([
                'exercise' => $exercise,
            ])
            ->execute();
    }

    public function countExerciseQuestion(Exercise $exercise)
    {
        return (int) $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(sq.question)
                FROM UJM\ExoBundle\Entity\Exercise AS e
                JOIN UJM\ExoBundle\Entity\Step AS s WITH s.exercise = e
                JOIN UJM\ExoBundle\Entity\StepItem AS sq WITH sq.step = s
                WHERE e = :exercise
            ')
            ->setParameter('exercise', $exercise)
            ->getSingleScalarResult();
    }
}
