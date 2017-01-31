<?php

namespace UJM\ExoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Question\Question;

/**
 * AnswerRepository.
 */
class AnswerRepository extends EntityRepository
{
    /**
     * Returns all answers to a question.
     * It can be limited to only one exercise.
     *
     * @param Question $question
     * @param Exercise $exercise
     *
     * @return Answer[]
     */
    public function findByQuestion(Question $question, Exercise $exercise = null)
    {
        return $this->createQueryBuilder('a')
            ->join('a.paper', 'p', 'WITH', 'p.exercise = :exercise')
            ->where('a.question = :question')
            ->setParameters([
                'exercise' => $exercise,
                'question' => $question,
            ])
            ->getQuery()
            ->getResult();
    }
}
