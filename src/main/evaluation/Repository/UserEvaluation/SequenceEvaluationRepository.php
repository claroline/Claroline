<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Repository\UserEvaluation;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Doctrine\ORM\EntityRepository;

class SequenceEvaluationRepository extends EntityRepository
{
    public function findByWorkspaceAndUser(Workspace $workspace, User $user): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.sequence', 's')
            ->where('s.workspace = :workspace')
            ->andWhere('e.user = :user')
            ->setParameter('workspace', $workspace)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function countByStatus(Sequence $sequence, Organization $organization): array
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT e.status, COUNT(e) AS value
                FROM Claroline\EvaluationBundle\Entity\UserEvaluation\SequenceEvaluation AS e
                LEFT JOIN e.user AS u
                LEFT JOIN u.userOrganizationReferences AS uo
                WHERE uo.organization = :organization 
                  AND e.sequence = :sequence
                GROUP BY e.status
                ORDER BY value DESC
           ')
            ->setParameter('organization', $organization)
            ->setParameter('sequence', $sequence)
            ->getResult();
    }
}
