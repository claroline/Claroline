<?php

namespace UJM\ExoBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Question;

class QuestionRepository extends EntityRepository
{
    /**
     * Returns all the questions created by a given user.
     *
     * @param User $user
     * @return array
     */
    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('q')
            ->join('q.user', 'u')
            ->join('q.category', 'c')
            ->where('q.user = :user')
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
            ->join('q.exerciseQuestions', 'eq')
            ->join('eq.exercise', 'e')
            ->where('e = :exercise')
            ->orderBy('eq.ordre')
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
    public function findByUserNotInExercise(User $user, Exercise $exercise, $limitToModels = false)
    {
        $exerciseQuestionsQuery = $this->createQueryBuilder('q1')
            ->join('q1.exerciseQuestions', 'eq')
            ->join('eq.exercise', 'e')
            ->where('e = :exercise');

        $qb = $this->createQueryBuilder('q')
            ->leftJoin('q.category', 'c')
            ->where('q.user = :user');

        if ($limitToModels) {
            $qb->andWhere('q.model = true');
        }

        return $qb
            ->andWhere($qb->expr()->notIn('q', $exerciseQuestionsQuery->getDQL()))
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
     * Returns the questions created by a user whose description
     * matches a given search string.
     *
     * @param User      $user
     * @param string    $description
     * @return array
     */
    public function findByUserAndDescription(User $user, $description)
    {
        return $this->createQueryBuilder('q')
            ->where('q.user = :user')
            ->andWhere('q.description LIKE :search')
            ->setParameters([
                'user' => $user,
                'search' => "%{$description}%"
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the questions created by a user whose category name,
     * type, title or description matches a given search string.
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
            ->orWhere('q.description LIKE :search');

        $parameters = [
            'user' => $user,
            'search' => "%{$content}%"
        ];

        if ($excluded) {
            $exerciseQuestionsQuery = $this->createQueryBuilder('q1')
                ->join('q1.exerciseQuestions', 'eq')
                ->join('eq.exercise', 'e')
                ->where('e = :exercise');
            $qb->andWhere(
                $qb->expr()->notIn('q', $exerciseQuestionsQuery->getDQL())
            );
            $parameters['exercise'] = $excluded;
        }

        return $qb->orderBy('c.value, q.title', 'ASC')
            ->setParameters($parameters)
            ->getQuery()
            ->getResult();
    }


    /**
     * Get user's Questions
     *
     * @access public
     *
     * @param integer $userId id User
     *
     * Return array[Question]
     */
    public function getQuestionsUser($userId)
    {
        $qb = $this->createQueryBuilder('q');
        $qb->join('q.user', 'u')
            ->where($qb->expr()->in('u.id', $userId));

        return $qb->getQuery()->getResult();
    }

    /**
     * Allow to know if the User is the owner of this Question
     *
     * @access public
     *
     * @param integer $user id User
     * @param integer $question id Question
     *
     * Return array[Question]
     */
    public function getControlOwnerQuestion($user, $question)
    {
        $qb = $this->createQueryBuilder('q');
        $qb->join('q.user', 'u')
            ->where($qb->expr()->in('q.id', $question))
            ->andWhere($qb->expr()->in('u.id', $user));

        return $qb->getQuery()->getResult();
    }

//    /**
//     * Search question by category
//     *
//     * @access public
//     *
//     * @param integer $userId id User
//     * @param String $whatToFind string to find
//     *
//     * Return array[Question]
//     */
//    public function findByCategory($userId, $whatToFind)
//    {
//        $dql = 'SELECT q FROM UJM\ExoBundle\Entity\Question q JOIN q.category c
//            WHERE c.value LIKE ?1
//            AND q.user = ?2';
//
//        $query = $this->_em->createQuery($dql)
//                      ->setParameters(array(1 => "%{$whatToFind}%", 2 => $userId));
//
//        return $query->getResult();
//    }

//    /**
//     * Search question
//     *
//     * @access public
//     *
//     * @param integer $userId id User
//     * @param String $whatToFind string to find
//     *
//     * Return array[Question]
//     */
//    public function findByTitle($userId, $whatToFind)
//    {
//        $dql = 'SELECT q FROM UJM\ExoBundle\Entity\Question q
//            WHERE q.title LIKE ?1
//            AND q.user = ?2';
//
//        $query = $this->_em->createQuery($dql)
//                      ->setParameters(array(1 => "%{$whatToFind}%", 2 => $userId));
//
//        return $query->getResult();
//    }
}
