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

use Claroline\ClacoFormBundle\Entity\Field;
use Doctrine\ORM\EntityRepository;

class FieldRepository extends EntityRepository
{
    /**
     * @return Field[]
     */
    public function findByFieldFacetUuid(string $fieldFacetId)
    {
        return $this->_em
            ->createQuery('
                SELECT f
                FROM Claroline\ClacoFormBundle\Entity\Field f
                JOIN f.fieldFacet ff
                WHERE ff.uuid = :uuid
            ')
            ->setParameter('uuid', $fieldFacetId)
            ->getOneOrNullResult();
    }
}
