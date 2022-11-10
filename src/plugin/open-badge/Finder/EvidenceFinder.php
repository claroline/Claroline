<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Doctrine\ORM\QueryBuilder;

class EvidenceFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Evidence::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $assertionJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'assertion':
                    if (!$assertionJoin) {
                        $qb->join('obj.assertion', 'a');
                        $assertionJoin = true;
                    }

                    $qb->andWhere('a.uuid = :assertion');
                    $qb->setParameter('assertion', $filterValue);

                    break;

                case 'recipient':
                    if (!$assertionJoin) {
                        $qb->join('obj.assertion', 'a');
                        $assertionJoin = true;
                    }

                    $qb->join('a.recipient', 'a');
                    $qb->andWhere('a.uuid = :recipientId');
                    $qb->setParameter('recipientId', $filterValue);

                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
