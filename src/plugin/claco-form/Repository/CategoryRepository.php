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

class CategoryRepository extends EntityRepository
{
    public function findAutoCategories(ClacoForm $clacoForm)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT c
                FROM Claroline\ClacoFormBundle\Entity\Category c
                JOIN c.clacoForm AS cf
                WHERE cf.id = :clacoFormId
                  AND EXISTS (
                    SELECT fc
                    FROM Claroline\ClacoFormBundle\Entity\FieldChoiceCategory AS fc
                    WHERE fc.category = c
                  )
            ')
            ->setParameter('clacoFormId', $clacoForm->getId())
            ->getResult();
    }
}
