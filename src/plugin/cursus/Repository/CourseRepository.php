<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CourseRepository extends EntityRepository
{
    public function search(string $search, int $nbResults)
    {
        return $this->createQueryBuilder('c')
            ->where('(UPPER(c.name) LIKE :search OR UPPER(c.code) LIKE :search)')
            ->andWhere('c.hidden = false')
            ->setFirstResult(0)
            ->setMaxResults($nbResults)
            ->setParameter('search', '%'.strtoupper($search).'%')
            ->getQuery()
            ->getResult();
    }
}
