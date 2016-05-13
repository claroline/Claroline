<?php

namespace UJM\ExoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Question;

class LinkHintPaperRepository extends EntityRepository
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
        return $this->createQueryBuilder('l')
            ->join('l.paper', 'p')
            ->join('l.hint', 'h')
            ->join('h.question', 'q')
            ->where('l.paper = :paper')
            ->andWhere('q = :question')
            ->andWhere('l.view = true')
            ->setParameters(['paper' => $paper, 'question' => $question])
            ->getQuery()
            ->getResult();
    }

    /**
     * Allow to know if a hint is viewed in an assessment.
     *
     *
     * @param int $hintID  id Hint
     * @param int $paperID id Paper
     *
     * Return array[LinkHintPaper]
     */
    public function getLHP($hintID, $paperID)
    {
        $qb = $this->createQueryBuilder('lhp');
        $qb->join('lhp.paper', 'p')
            ->join('lhp.hint', 'h')
            ->where($qb->expr()->in('p.id', $paperID))
            ->andWhere($qb->expr()->in('h.id', $hintID));

        return $qb->getQuery()->getResult();
    }

    /**
     * Get hint viewed for a paper.
     *
     *
     * @param int $paperID id Paper
     *
     * Return array[LinkHintPaper]
     */
    public function getHintViewed($paperID)
    {
        $qb = $this->createQueryBuilder('lhp');
        $qb->where($qb->expr()->in('lhp.paper', $paperID))
            ->andWhere($qb->expr()->in('lhp.view', 1));

        return $qb->getQuery()->getResult();
    }
}
