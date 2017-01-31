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

use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\EntityRepository;

class FieldRepository extends EntityRepository
{
    public function findFieldByNameExcludingId(ClacoForm $clacoForm, $name, $id)
    {
        $dql = '
            SELECT f
            FROM Claroline\ClacoFormBundle\Entity\Field f
            JOIN f.clacoForm c
            WHERE c = :clacoForm
            AND UPPER(f.name) = :name
            AND f.id != :id
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('clacoForm', $clacoForm);
        $upperName = strtoupper($name);
        $query->setParameter('name', $upperName);
        $query->setParameter('id', $id);

        return $query->getOneOrNullResult();
    }

    public function findNonConfidentialFieldsByResourceNode(ResourceNode $resourceNode)
    {
        $dql = '
            SELECT f
            FROM Claroline\ClacoFormBundle\Entity\Field f
            JOIN f.clacoForm c
            JOIN c.resourceNode r
            WHERE r = :resourceNode
            AND f.isMetadata = false
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('resourceNode', $resourceNode);

        return $query->getResult();
    }
}
