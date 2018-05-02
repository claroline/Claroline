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

use Claroline\AppBundle\API\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.clacoform.category")
 * @DI\Tag("claroline.finder")
 */
class CategoryFinder implements FinderInterface
{
    public function getClass()
    {
        return 'Claroline\ClacoFormBundle\Entity\Category';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        $qb->join('obj.clacoForm', 'cf');
        $qb->andWhere('cf.id = :clacoFormId');
        $qb->setParameter('clacoFormId', $searches['clacoForm']);
        $managersJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'clacoForm':
                    break;
                case 'managers':
                    $where = "CONCAT(UPPER(m.firstName), CONCAT(' ', UPPER(m.lastName))) LIKE :{$filterName}";
                    $where .= " OR CONCAT(UPPER(m.lastName), CONCAT(' ', UPPER(m.firstName))) LIKE :{$filterName}";
                    $qb->join('obj.managers', 'm');
                    $qb->andWhere($where);
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    $managersJoin = true;
                    break;
                case 'notify_addition':
                case 'notify_edition':
                case 'notify_removal':
                case 'notify_pending_comment':
                    $value = '%"'.$filterName.'":';
                    $value .= $filterValue ? 'true' : 'false';
                    $value .= '%';
                    $qb->andWhere("obj.details LIKE :{$filterName}");
                    $qb->setParameter($filterName, $value);
                    break;
                default:
                    if (is_bool($filterValue)) {
                        $qb->andWhere("obj.{$filterName} = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    } else {
                        $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                        $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    }
            }
        }
        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'managers':
                    if (!$managersJoin) {
                        $qb->join('obj.managers', 'm');
                    }
                    $qb->orderBy('m.lastName', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
