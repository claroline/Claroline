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
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{
    public function findCategoriesByManager(ClacoForm $clacoForm, User $manager)
    {
        $dql = '
            SELECT c
            FROM Claroline\ClacoFormBundle\Entity\Category c
            JOIN c.clacoForm cf
            JOIN c.managers m
            WHERE cf = :clacoForm
            AND m = :manager
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('clacoForm', $clacoForm);
        $query->setParameter('manager', $manager);

        return $query->getResult();
    }

    public function findCategoriesByIds(array $ids)
    {
        $dql = '
            SELECT c
            FROM Claroline\ClacoFormBundle\Entity\Category c
            WHERE c.id IN (:ids)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('ids', $ids);

        return $query->getResult();
    }
}
