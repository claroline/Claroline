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
use Doctrine\ORM\EntityRepository;

class FieldValueRepository extends EntityRepository
{
    public function findFieldValuesByType(ClacoForm $clacoForm, $type)
    {
        $dql = '
            SELECT fv
            FROM Claroline\ClacoFormBundle\Entity\FieldValue fv
            JOIN fv.field f
            JOIN f.clacoForm c
            WHERE c = :clacoForm
            AND f.type = :type
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('clacoForm', $clacoForm);
        $query->setParameter('type', $type);

        return $query->getResult();
    }
}
