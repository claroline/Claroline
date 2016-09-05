<?php

namespace UJM\ExoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Question;

class HintRepository extends EntityRepository
{
    /**
     * Returns all the hint links marked as "viewed" which are associated
     * with a question in a given paper.
     *
     * @param Paper    $paper
     * @param Question $question
     *
     * @return array
     */
    public function findViewedByPaperAndQuestion(Paper $paper, Question $question)
    {
        return $this->createQueryBuilder('h')
            ->join('UJM\ExoBundle\Entity\LinkHintPaper', 'l', Join::WITH, 'l.hint = h')
            ->join('l.paper', 'p')
            ->join('h.question', 'q')
            ->where('l.paper = :paper')
            ->andWhere('q = :question')
            ->andWhere('l.view = true')
            ->setParameters(['paper' => $paper, 'question' => $question])
            ->getQuery()
            ->getResult();
    }
}
