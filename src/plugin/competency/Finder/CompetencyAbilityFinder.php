<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeVinci\CompetencyBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;
use HeVinci\CompetencyBundle\Entity\CompetencyAbility;

class CompetencyAbilityFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return CompetencyAbility::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $competencyJoin = false;
        $abilityJoin = false;
        $levelJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'competencies':
                    if (!$competencyJoin) {
                        $qb->join('obj.competency', 'c');
                        $competencyJoin = true;
                    }
                    $qb->andWhere('c.uuid IN (:competencyIds)');
                    $qb->setParameter('competencyIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'abilities':
                    if (!$abilityJoin) {
                        $qb->join('obj.ability', 'a');
                        $abilityJoin = true;
                    }
                    $qb->andWhere('a.uuid IN (:abilityIds)');
                    $qb->setParameter('abilityIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'ability.name':
                    if (!$abilityJoin) {
                        $qb->join('obj.ability', 'a');
                        $abilityJoin = true;
                    }
                    $qb->andWhere('UPPER(a.name) LIKE :abilityName');
                    $qb->setParameter('abilityName', '%'.strtoupper($filterValue).'%');
                    break;
                case 'levels':
                    if (!$levelJoin) {
                        $qb->join('obj.level', 'l');
                        $levelJoin = true;
                    }
                    $qb->andWhere('l.uuid IN (:levelIds)');
                    $qb->setParameter('levelIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'level.name':
                    if (!$levelJoin) {
                        $qb->join('obj.level', 'l');
                        $levelJoin = true;
                    }
                    $qb->andWhere('UPPER(l.name) LIKE :levelName');
                    $qb->setParameter('levelName', '%'.strtoupper($filterValue).'%');
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }
        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'ability.name':
                    if (!$abilityJoin) {
                        $qb->join('obj.ability', 'a');
                    }
                    $qb->orderBy('a.name', $sortByDirection);
                    break;
                case 'level.name':
                    if (!$levelJoin) {
                        $qb->join('obj.level', 'l');
                    }
                    $qb->orderBy('l.name', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
