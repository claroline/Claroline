<?php

namespace UJM\ExoBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Question;

class QuestionRepository extends EntityRepository
{
    /**
     * Returns all the questions created by a given user. Allows to
     * select only questions defined as models (defaults to false).
     *
     * @param User $user
     * @param bool $limitToModels
     * @return array
     */
    public function findByUser(User $user, $limitToModels = false)
    {
        $qb = $this->createQueryBuilder('q')
            ->join('q.user', 'u')
            ->join('q.category', 'c')
            ->where('q.user = :user');

        if ($limitToModels) {
            $qb->andWhere('q.model = true');
        }

        return $qb
            ->orderBy('c.value, q.title', 'ASC')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns all the questions linked to a given exercise.
     *
     * @param Exercise $exercise
     * @return Question[]
     */
    public function findByExercise(Exercise $exercise)
    {
        return $this->createQueryBuilder('q')
            ->join('q.stepQuestions', 'sq')
            ->join('sq.step', 's')
            ->where('s = :exercise')
            ->orderBy('sq.ordre')
            ->setParameter(':exercise', $exercise)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the questions corresponding to an array of ids.
     *
     * @param array $ids
     * @return Question[]
     */
    public function findByIds(array $ids)
    {
        return $this->createQueryBuilder('q')
            ->where('q IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the questions created by a user which are not
     * associated with a given exercise. Allows to select only
     * questions defined as models (defaults to false).
     *
     * @param User      $user
     * @param Exercise  $exercise
     * @param bool      $limitToModels
     * @return array
     */
    public function findByUserNotInExercise(
        User $user,
        Exercise $exercise,
        $limitToModels = false
    )
    {
        $stepQuestionsQuery = $this->createQueryBuilder('q1')
            ->join('q1.stepQuestions', 'sq')
            ->join('sq.step', 's')
            ->where('s = :exercise');

        $qb = $this->createQueryBuilder('q')
            ->leftJoin('q.category', 'c')
            ->where('q.user = :user');

        if ($limitToModels) {
            $qb->andWhere('q.model = true');
        }

        return $qb
            ->andWhere($qb->expr()->notIn('q', $stepQuestionsQuery->getDQL()))
            ->orderBy('c.value, q.title', 'ASC')
            ->setParameters([
                'user' => $user,
                'exercise' => $exercise
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the questions created by a user whose category
     * name matches a given search string.
     *
     * @param User      $user
     * @param string    $categoryName
     * @return array
     */
    public function findByUserAndCategoryName(User $user, $categoryName)
    {
        return $this->createQueryBuilder('q')
            ->join('q.category', 'c')
            ->where('q.user = :user')
            ->andWhere('c.value LIKE :search')
            ->setParameters([
                'user' => $user,
                'search' => "%{$categoryName}%"
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the questions created by a user whose type
     * matches a given search string.
     *
     * @param User      $user
     * @param string    $type
     * @return array
     */
    public function findByUserAndType(User $user, $type)
    {
        return $this->createQueryBuilder('q')
            ->where('q.user = :user')
            ->andWhere('q.type LIKE :search')
            ->setParameters([
                'user' => $user,
                'search' => "%{$type}%"
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the questions created by a user whose title
     * matches a given search string.
     *
     * @param User      $user
     * @param string    $title
     * @return array
     */
    public function findByUserAndTitle(User $user, $title)
    {
        return $this->createQueryBuilder('q')
            ->where('q.user = :user')
            ->andWhere('q.title LIKE :search')
            ->setParameters([
                'user' => $user,
                'search' => "%{$title}%"
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the questions created by a user whose invite
     * matches a given search string.
     *
     * @param User      $user
     * @param string    $invite
     * @return array
     */
    public function findByUserAndInvite(User $user, $invite)
    {
        return $this->createQueryBuilder('q')
            ->where('q.user = :user')
            ->andWhere('q.invite LIKE :search')
            ->setParameters([
                'user' => $user,
                'search' => "%{$invite}%"
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the questions created by a user whose category name,
     * type, title or invite matches a given search string.
     * Allows to select only questions which are not associated with
     * a particular exercise.
     *
     * @param User $user
     * @param string $content
     * @param Exercise $excluded
     * @return array
     */
    public function findByUserAndContent(
        User $user,
        $content,
        Exercise $excluded = null
    )
    {
        $qb = $this->createQueryBuilder('q')
            ->leftJoin('q.category', 'c')
            ->where('q.user = :user')
            ->andWhere('c.value LIKE :search')
            ->orWhere('q.type LIKE :search')
            ->orWhere('q.title LIKE :search')
            ->orWhere('q.invite LIKE :search');

        $parameters = [
            'user' => $user,
            'search' => "%{$content}%"
        ];

        if ($excluded) {
            $stepQuestionsQuery = $this->createQueryBuilder('q1')
                ->join('q1.stepQuestions', 'sq')
                ->join('sq.step', 's')
                ->where('s = :exercise');
            $qb->andWhere(
                $qb->expr()->notIn('q', $stepQuestionsQuery->getDQL())
            );
            $parameters['exercise'] = $excluded;
        }

        return $qb->orderBy('c.value, q.title', 'ASC')
            ->setParameters($parameters)
            ->getQuery()
            ->getResult();
    }
}
