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

use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Doctrine\ORM\EntityRepository;

class ToolRightsRepository extends EntityRepository
{
    /**
     * Returns the maximum rights on a given resource for a set of roles.
     * Used by the ResourceVoter.
     *
     * @param string[]    $roles
     * @param OrderedTool $orderedTool
     *
     * @return int
     */
    public function findMaximumRights(array $roles, OrderedTool $orderedTool)
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
            WHERE ';

        $index = 0;

        foreach ($roles as $key => $role) {
            $dql .= 0 !== $index ? ' OR ' : '';
            $dql .= "ot.id = {$orderedTool->getId()} AND role.name = :role{$key}";
            ++$index;
        }

        $query = $this->_em->createQuery($dql);

        foreach ($roles as $key => $role) {
            $query->setParameter("role{$key}", $role);
        }

        $results = $query->getResult();
        $mask = 0;

        foreach ($results as $result) {
            $mask |= $result['mask'];
        }

        return $mask;
    }
}
