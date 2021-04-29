<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;

class KeywordFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return 'Claroline\ClacoFormBundle\Entity\Keyword';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $qb->join('obj.clacoForm', 'cf');
        $qb->andWhere('cf.id = :clacoFormId');
        $qb->setParameter('clacoFormId', $searches['clacoForm']);

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'clacoForm':
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
