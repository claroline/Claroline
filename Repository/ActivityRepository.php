<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ActivityRepository extends EntityRepository
{
    public function findActivitiesByNodeIds(array $resourcesId)
    {
        if (count($resourcesId) === 0) {
            throw new \InvalidArgumentException("Array argument cannot be empty");
        }

        $index = 0;
        $eol = PHP_EOL;
        $resourcesIdTest = "(";

        foreach ($resourcesId as $resId) {
            $resourcesIdTest .= $index > 0 ? "    OR " : "    ";
            $resourcesIdTest .= "node.id = {$resId}{$eol}";
            $index++;
        }
        $resourcesIdTest .= "){$eol}";
        $dql = "
            SELECT a.id, a.instructions, a.startDate, a.endDate, node.id as nodeId
            FROM Claroline\CoreBundle\Entity\Resource\Activity a
            JOIN a.resourceNode node
            WHERE {$resourcesIdTest}
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}
