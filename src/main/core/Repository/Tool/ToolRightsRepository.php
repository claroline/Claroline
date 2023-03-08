<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Tool;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class ToolRightsRepository extends EntityRepository
{
    /**
     * Returns the maximum rights on a given tool for a set of roles.
     */
    public function findMaximumRights(array $roles, Tool $tool, Workspace $workspace = null): int
    {
        //add the role anonymous for everyone !
        if (!in_array('ROLE_ANONYMOUS', $roles)) {
            $roles[] = 'ROLE_ANONYMOUS';
        }

        $dql = '
            SELECT tr.mask
            FROM Claroline\CoreBundle\Entity\Tool\ToolRights AS tr
            JOIN tr.role AS role
            JOIN tr.orderedTool AS ot
            JOIN ot.tool AS t
            WHERE t.id = :toolId
              AND role.name IN (:roles)
        ';

        if (!empty($workspace)) {
            $dql .= ' AND ot.workspace = :workspace';
        } else {
            $dql .= ' AND ot.workspace IS NULL';
        }

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('toolId', $tool->getId())
            ->setParameter('roles', $roles);

        if (!empty($workspace)) {
            $query->setParameter('workspace', $workspace);
        }

        $results = $query->getResult();

        $mask = 0;
        foreach ($results as $result) {
            $mask |= $result['mask'];
        }

        return $mask;
    }
}
