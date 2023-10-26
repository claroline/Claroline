<?php

namespace Claroline\FlashcardBundle\Repository;

use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Doctrine\ORM\EntityRepository;

class CardDrawnProgressionRepository extends EntityRepository
{
    public function findBySuccessCount( ResourceEvaluation $resourceEvaluation )
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->where($qb->expr()->eq('p.resourceEvaluation', ':resourceEvaluation'))
            ->andWhere($qb->expr()->eq('p.successCount', ':successCount'))
            ->setParameter('resourceEvaluation', $resourceEvaluation)
            ->setParameter('successCount', 1)
            ->getQuery()
            ->getResult()
        ;
    }
}
