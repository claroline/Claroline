<?php

namespace Claroline\CoreBundle\API\Finder\Log;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\Log\Log;
use Doctrine\ORM\QueryBuilder;

class LogFinder extends AbstractFinder
{
    /**
     * The queried object is already named "obj".
     */
    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $qb->leftJoin('obj.resourceType', 'ort');
        $userJoin = false;
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'resourceType':
                    if (is_array($filterValue)) {
                        $qb->andWhere("ort.name IN (:{$filterName})");
                    } else {
                        $qb->andWhere("ort.name LIKE :{$filterName}");
                    }
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'doer':
                    if (!$userJoin) {
                        $userJoin = true;
                        $qb->join('obj.doer', 'doer');
                    }

                    if (is_numeric($filterValue)) {
                        $qb->andWhere("doer.id = :{$filterName}");
                    } else {
                        $qb->andWhere("doer.uuid = :{$filterName}");
                    }
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'doer.name':
                    if (!$userJoin) {
                        $userJoin = true;
                        $qb->join('obj.doer', 'doer');
                    }
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->like('UPPER(doer.firstName)', ':doer'),
                        $qb->expr()->like('UPPER(doer.lastName)', ':doer'),
                        $qb->expr()->like('UPPER(doer.username)', ':doer'),
                        $qb->expr()->like('UPPER(doer.email)', ':doer'),
                        $qb->expr()->like(
                            "CONCAT(CONCAT(UPPER(doer.firstName), ' '), UPPER(doer.lastName))",
                            ':doer'
                        ),
                        $qb->expr()->like(
                            "CONCAT(CONCAT(UPPER(doer.lastName), ' '), UPPER(doer.firstName))",
                            ':doer'
                        )
                    ));
                    $qb->setParameter('doer', '%'.strtoupper($filterValue).'%');
                    break;
                case 'doerRoles':
                    if (!$userJoin) {
                        $userJoin = true;
                        $qb->join('obj.doer', 'doer');
                    }

                    $qb
                        ->join('doer.roles', 'roles')
                        ->andWhere('roles.id IN (:roleIds)')
                        ->setParameter('roleIds', $filterValue);

                    break;
                case 'doerActive':
                    if (!$userJoin) {
                        $userJoin = true;
                        $qb->join('obj.doer', 'doer');
                    }
                    $qb->andWhere('doer.isRemoved = false');
                    $qb->andWhere('doer.isEnabled = true');

                    break;
                case 'doerCreated':
                    if (!$userJoin) {
                        $userJoin = true;
                        $qb->join('obj.doer', 'doer');
                    }

                    $qb
                        ->andWhere('doer.created <= :date')
                        ->setParameter('date', $filterValue);

                    break;
                case 'dateLog':
                    $qb->andWhere('obj.dateLog >= :dateFrom')
                        ->setParameter('dateFrom', $filterValue);
                    break;
                case 'dateFromStrict':
                    $qb->andWhere('obj.dateLog > :dateFromStrict')
                        ->setParameter('dateFromStrict', $filterValue);
                    break;
                case 'dateTo':
                    $qb->andWhere('obj.dateLog <= :dateTo')
                        ->setParameter('dateTo', $filterValue);
                    break;
                case 'dateToStrict':
                    $qb->andWhere('obj.dateLog < :dateToStrict')
                        ->setParameter('dateToStrict', $filterValue);
                    break;
                case 'action':
                    $this->filterAction($filterValue, $qb);
                    break;
                case 'organization':
                    if (!$userJoin) {
                        $userJoin = true;
                        $qb->join('obj.doer', 'doer');
                    }
                    $qb->join('doer.userOrganizationReferences', 'orgaRef')
                        ->andWhere('orgaRef.organization IN (:organizations)')
                        ->setParameter('organizations', $filterValue);
                    break;
                case 'unique':
                case 'type':
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
                    break;
            }
        }

        if (!empty($sortBy) && ('doer.name' === $sortBy['property'] || 'actions' === $sortBy['property'])) {
            $direction = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';
            switch ($sortBy['property']) {
                case 'doer.name':
                    if (!$userJoin) {
                        $qb->join('obj.doer', 'doer');
                    }
                    $qb->addOrderBy('doer.lastName', $direction);
                    $qb->addOrderBy('doer.firstName', $direction);
                    $qb->addOrderBy('doer.id', $direction);
                    break;
                case 'actions':
                    $qb->addOrderBy('actions', $direction);
            }
        }

        return $qb;
    }

    /** @return $string */
    public static function getClass(): string
    {
        return Log::class;
    }

    private function filterAction($action, QueryBuilder $qb)
    {
        if ('all' === $action) {
            return;
        }

        $actionChunks = explode('::', $action);
        if (count($actionChunks) < 2) {
            $qb
                ->andWhere('obj.action = :action')
                ->setParameter('action', $action);

            return;
        }
        if (2 === count($actionChunks) && 'all' === $actionChunks[1]) {
            $qb
                ->andWhere('obj.action LIKE :action')
                ->setParameter('action', $actionChunks[0].'%');

            return;
        }
        if ('resource' === $actionChunks[0]) {
            $qb
                ->andWhere('ort.name = :type')
                ->setParameter('type', $actionChunks[1]);
            if ('all' !== $actionChunks[2]) {
                $qb->andWhere('obj.action = :action')
                    ->setParameter('action', $actionChunks[2]);
            }
        }
    }
}
