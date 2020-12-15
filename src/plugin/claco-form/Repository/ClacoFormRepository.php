<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ClacoFormRepository extends EntityRepository
{
    public function findClacoFormByResourceNodeId($resourceNodeId)
    {
        $dql = '
            SELECT c
            FROM Claroline\ClacoFormBundle\Entity\ClacoForm c
            JOIN c.resourceNode r
            WHERE r.id = :resourceNodeId
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('resourceNodeId', $resourceNodeId);

        return $query->getOneOrNullResult();
    }
}
