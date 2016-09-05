<?php

namespace UJM\ExoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use UJM\ExoBundle\Entity\LinkHintPaper;

class LinkHintPaperRepository extends EntityRepository
{
    /**
     * Allow to know if a hint is viewed in an assessment.
     *
     * @param int $hintID  Hint ID
     * @param int $paperID Paper ID
     *
     * @return LinkHintPaper[]
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
}
