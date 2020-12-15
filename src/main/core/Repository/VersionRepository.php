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

class VersionRepository extends EntityRepository
{
    public function getLatest($fqcn)
    {
        return $this->createQueryBuilder('e')->
            orderBy('e.date', 'DESC')->
            where('e.bundle LIKE :bundle')->
            setParameter('bundle', "%{$fqcn}%")->
            setMaxResults(1)->
            getQuery()->
            getOneOrNullResult();
    }

    public function getLatestExecuted($fqcn)
    {
        $fqcn = addcslashes($fqcn, '\\');

        $query = $this->createQueryBuilder('e')
            ->orderBy('e.date', 'DESC')
            ->where('e.isUpgraded = TRUE')
            ->andWhere('e.bundle LIKE :bundle')
            ->setMaxResults(1)
            ->setParameter('bundle', "%{$fqcn}%")
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
