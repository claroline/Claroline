<?php

namespace UJM\ExoBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Hint;

/**
 * PaperRepository.
 */
class PaperRepository extends EntityRepository
{
    /**
     * Returns the last paper (finished or not) done by a User.
     * Mostly use to know the next paper number.
     *
     * @param Exercise $exercise
     * @param User     $user
     *
     * @return Paper
     */
    public function findLastPaper(Exercise $exercise, User $user)
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.exercise = :exercise')
            ->orderBy('p.number', 'DESC')
            ->setMaxResults(1)
            ->setParameters([
                'user' => $user,
                'exercise' => $exercise,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Returns the unfinished papers of a user for a given exercise, if any.
     *
     * @param Exercise $exercise
     * @param User     $user
     *
     * @return Paper[]
     */
    public function findUnfinishedPapers(Exercise $exercise, User $user)
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.exercise = :exercise')
            ->andWhere('p.end IS NULL')
            ->orderBy('p.start', 'DESC')
            ->setParameters([
                'user' => $user,
                'exercise' => $exercise,
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the unfinished papers of a user for a given exercise for the current day, if any.
     *
     * @param Exercise $exercise
     * @param User     $user
     *
     * @return Paper[]
     */
    public function countUserFinishedDayPapers(Exercise $exercise, User $user)
    {
        $datetime = new \DateTime();
        $timestamp = $datetime->getTimeStamp();
        $today = strtotime('midnight', $timestamp);
        $tomorrow = strtotime('tomorrow', $today) - 1;

        return (int) $this->getEntityManager()
          ->createQuery('
              SELECT COUNT(p)
              FROM UJM\ExoBundle\Entity\Attempt\Paper AS p
              WHERE p.user = :user
                AND p.exercise = :exercise
                AND p.end >= :today
                AND p.end <= :tomorrow
          ')
          ->setParameters([
              'user' => $user,
              'exercise' => $exercise,
              'today' => $today,
              'tomorrow' => $tomorrow,
          ])
          ->getSingleScalarResult();
    }

    /**
     * Finds the score of a paper by summing the score of each answer.
     *
     * @param Paper $paper
     *
     * @return float
     */
    public function findScore(Paper $paper)
    {
        return (float) $this->getEntityManager()
            ->createQuery('
                SELECT SUM(a.score)
                FROM UJM\ExoBundle\Entity\Attempt\Answer AS a
                WHERE a.paper = :paper
                  AND a.score IS NOT NULL
            ')
            ->setParameters([
                'paper' => $paper,
            ])
            ->getSingleScalarResult();
    }

    /**
     * Checks that all the answers of a Paper have been marked.
     *
     * @param Paper $paper
     *
     * @return bool
     */
    public function isFullyEvaluated(Paper $paper)
    {
        return 0 === (int) $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(a)
                FROM UJM\ExoBundle\Entity\Attempt\Answer AS a
                WHERE a.paper = :paper
                  AND a.score IS NULL
            ')
            ->setParameters([
                'paper' => $paper,
            ])
            ->getSingleScalarResult();
    }

    /**
     * Returns the number of papers for an exercise.
     *
     * @param Exercise $exercise
     *
     * @return int the number of exercise papers
     */
    public function countExercisePapers(Exercise $exercise)
    {
        return (int) $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(p)
                FROM UJM\ExoBundle\Entity\Attempt\Paper AS p
                WHERE p.exercise = :exercise
            ')
            ->setParameters([
                'exercise' => $exercise,
            ])
            ->getSingleScalarResult();
    }

    /**
     * Returns the number of registered users associated to a given exercise.
     *
     * @param Exercise $exercise
     *
     * @return int the number of registered users
     */
    public function countPapersUsers(Exercise $exercise)
    {
        return (int) $this->getEntityManager()
          ->createQuery('
              SELECT COUNT(distinct p.user)
              FROM UJM\ExoBundle\Entity\Attempt\Paper AS p
              WHERE p.exercise = :exercise
                AND p.user IS NOT NULL
          ')
          ->setParameters([
              'exercise' => $exercise,
          ])
          ->getSingleScalarResult();
    }

    /**
     * Returns the number of annymous users associated to a given exercise.
     *
     * @param Exercise $exercise
     *
     * @return int the number of registered users
     */
    public function countAnonymousPapers(Exercise $exercise)
    {
        return (int) $this->getEntityManager()
          ->createQuery('
              SELECT COUNT(p.id)
              FROM UJM\ExoBundle\Entity\Attempt\Paper AS p
              WHERE p.exercise = :exercise
                AND p.user IS NULL
          ')
          ->setParameters([
              'exercise' => $exercise,
          ])
          ->getSingleScalarResult();
    }

    /**
     * Finds papers of an exercise that needs correction (aka papers that have answers with `null` score).
     *
     * @param Exercise $exercise
     *
     * @return Paper[]
     */
    public function findPapersToCorrect(Exercise $exercise)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT p
                FROM UJM\ExoBundle\Entity\Attempt\Paper AS p
                JOIN UJM\ExoBundle\Entity\Attempt\Answer AS a WITH (a.paper = p)
                WHERE p.exercise = :exercise
                  AND p.end IS NOT NULL
                  AND a.score IS NULL
                ORDER BY p.start ASC
            ')
            ->setParameters([
                'exercise' => $exercise,
            ])
            ->getResult();
    }

    /**
     * Counts the number of finished paper for a user and an exercise.
     *
     * @param Exercise $exercise
     * @param User     $user
     *
     * @return int the number of finished papers
     */
    public function countUserFinishedPapers(Exercise $exercise, User $user)
    {
        return (int) $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(p)
                FROM UJM\ExoBundle\Entity\Attempt\Paper AS p
                WHERE p.user = :user
                  AND p.exercise = :exercise
                  AND p.end IS NOT NULL
            ')
            ->setParameters([
                'user' => $user,
                'exercise' => $exercise,
            ])
            ->getSingleScalarResult();
    }

    /**
     * Returns whether a hint is related to a paper.
     *
     * @param Paper $paper
     * @param Hint  $hint
     *
     * @return bool
     */
    public function hasHint(Paper $paper, Hint $hint)
    {
        return 0 < (int) $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->join('p.exercise', 'e')
            ->join('e.steps', 's')
            ->join('s.stepQuestions', 'sq')
            ->where('e = :exercise')
            ->andWhere('sq.question = :question')
            ->setParameters([
                'question' => $hint->getQuestion(),
                'exercise' => $paper->getExercise(),
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
