<?php

namespace UJM\ExoBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Item;

/**
 * AnswerRepository.
 */
class AnswerRepository extends EntityRepository
{
    /**
     * Returns all answers to a question.
     * It can be limited to only one exercise.
     *
     * @param Exercise $exercise
     * @param bool     $finishedPapersOnly
     *
     * @return Answer[]
     */
    public function findByQuestion(Item $question, Exercise $exercise = null, $finishedPapersOnly = false)
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.paper', 'p', 'WITH', 'p.exercise = :exercise')
            ->where('a.questionId = :question')
            ->setParameters([
                'exercise' => $exercise,
                'question' => $question->getUuid(),
            ]);

        if ($finishedPapersOnly) {
            $qb->andWhere('p.end IS NOT NULL');
        }

        return $qb->getQuery()->getResult();
    }

    public function getAvgScoreByAttempts(Exercise $exercise, bool $finishedOnly = false, User $user = null)
    {
        $parameters = [
            'exercise' => $exercise,
        ];

        $dql = '
          SELECT p.number, a.questionId, AVG(a.score) AS score
                FROM UJM\ExoBundle\Entity\Attempt\Answer AS a
                LEFT JOIN UJM\ExoBundle\Entity\Attempt\Paper AS p WITH a.paper = p
                WHERE p.exercise = :exercise
                  AND p.total IS NOT NULL
                  AND a.score IS NOT NULL 
        ';

        if ($finishedOnly) {
            $dql .= 'AND p.end IS NOT NULL ';
        }

        if ($user) {
            $dql .= 'AND p.user = :user ';

            $parameters['user'] = $user;
        }

        $dql .= '
            GROUP BY p.number, a.questionId
            ORDER BY p.number ASC
        ';

        return $this->getEntityManager()
            ->createQuery($dql)
            ->setParameters($parameters)
            ->getArrayResult();
    }
}
