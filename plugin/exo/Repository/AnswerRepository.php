<?php

namespace UJM\ExoBundle\Repository;

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
     * @param Item     $question
     * @param Exercise $exercise
     *
     * @return Answer[]
     */
    public function findByQuestion(Item $question, Exercise $exercise = null)
    {
        return $this->createQueryBuilder('a')
            ->join('a.paper', 'p', 'WITH', 'p.exercise = :exercise')
            ->where('a.questionId = :question')
            ->setParameters([
                'exercise' => $exercise,
                'question' => $question->getUuid(),
            ])
            ->getQuery()
            ->getResult();
    }
}
