<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\Repository;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class QuestionRepository extends EntityRepository
{
    public function findQuestionById($questionId, $executeQuery = true)
    {
        $dql = "
            SELECT q
            FROM Claroline\SurveyBundle\Entity\Question q
            WHERE q.id = :id
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('id', $questionId);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findQuestionsByWorkspace(
        Workspace $workspace,
        $orderedBy = 'title',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT q
            FROM Claroline\SurveyBundle\Entity\Question q
            WHERE q.workspace = :workspace
            AND q.type != 'title'
            ORDER BY q.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findQuestionsByWorkspaceWithExclusions(
        Workspace $workspace,
        array $exclusions,
        $orderedBy = 'title',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT q
            FROM Claroline\SurveyBundle\Entity\Question q
            WHERE q.workspace = :workspace
            AND q.type != 'title'
            AND q.id NOT IN (:exclusions)
            ORDER BY q.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('exclusions', $exclusions);

        return $executeQuery ? $query->getResult() : $query;
    }
}
