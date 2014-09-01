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

class QuestionModelRepository extends EntityRepository
{
    public function findModelsByWorkspace(
        Workspace $workspace,
        $orderedBy = 'title',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT qm
            FROM Claroline\SurveyBundle\Entity\QuestionModel qm
            WHERE qm.workspace = :workspace
            ORDER BY qm.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $executeQuery ? $query->getResult() : $query;
    }
}
