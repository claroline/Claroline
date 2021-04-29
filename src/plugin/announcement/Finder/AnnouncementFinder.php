<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Finder;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;

class AnnouncementFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Announcement::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $qb->leftJoin('obj.aggregate', 'a');
        $qb->leftJoin('a.resourceNode', 'node');

        $workspaceJoin = false;
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'published':
                    $qb->andWhere('node.active = :active');
                    $qb->andWhere('node.published = :published');
                    $qb->setParameter('active', $filterValue);
                    $qb->setParameter('published', $filterValue);
                    break;
                case 'creator':
                    $qb->leftJoin('obj.creator', 'creator');
                    $qb->andWhere("creator.username LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.$filterValue.'%');
                    break;
                case 'workspace':
                    if (!$workspaceJoin) {
                        $qb->join('node.workspace', 'w');

                        $workspaceJoin = true;
                    }

                    $qb->andWhere("w.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'archived':
                    if (!$workspaceJoin) {
                        $qb->join('node.workspace', 'w');

                        $workspaceJoin = true;
                    }

                    $qb->andWhere("w.archived = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'meta.publishedAt':
                    break;
                case 'notDoneYet':
                    $now = new \DateTime();
                    if ($filterValue) {
                        $qb->andWhere("obj.publicationDate >= :{$filterName}");
                    } else {
                        $qb->andWhere("obj.publicationDate <= :{$filterName}");
                    }
                    $qb->setParameter($filterName, $now);
                    break;
                case 'visible':
                    if ($filterValue) {
                        $now = new \DateTime();
                        $expr = [];
                        $expr[] = $qb->expr()->orX(
                            $qb->expr()->gte('obj.visibleFrom', $now),
                            $qb->expr()->isNull('obj.visibleFrom')
                        );
                        $expr[] = $qb->expr()->orX(
                            $qb->expr()->lte('obj.visibleUntil', $now),
                            $qb->expr()->isNull('obj.visibleUntil')
                        );
                        $qb->expr()->andX(...$expr);
                        $qb->andWhere('obj.visible = true');
                    }
                    break;
                case 'roles':
                    $managerRoles = [];
                    $otherRoles = [];

                    foreach ($filterValue as $roleName) {
                        if (preg_match('/^ROLE_WS_MANAGER_/', $roleName)) {
                            $managerRoles[] = $roleName;
                        } else {
                            $otherRoles[] = $roleName;
                        }
                    }

                    $managerSearch = $roleSearch = $searches;
                    $managerSearch['_managerRoles'] = $managerRoles;
                    $roleSearch['_roles'] = $otherRoles;
                    unset($managerSearch['roles']);
                    unset($roleSearch['roles']);

                    return $this->union($managerSearch, $roleSearch, $options, $sortBy);

                    break;
                case '_managerRoles':
                    if (!$workspaceJoin) {
                        $qb->join('node.workspace', 'w');

                        $workspaceJoin = true;
                    }

                    $qb->leftJoin('w.roles', 'owr');
                    $qb->andWhere('owr.name IN (:managerRoles)');

                    $qb->setParameter('managerRoles', $filterValue);
                    break;
                case '_roles':
                    $qb->leftJoin('node.rights', 'rights');
                    $qb->join('rights.role', 'rightsr');
                    $qb->andWhere('rightsr.name IN (:otherRoles)');
                    $qb->andWhere('BIT_AND(rights.mask, 1) = 1');
                    $qb->setParameter('otherRoles', $filterValue);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'meta.publishedAt':
                    $qb->orderBy('obj.publicationDate', $sortByDirection);
                    break;
            }
        }
    }

    //required for the unions
    public function getExtraFieldMapping()
    {
        return [
          'meta.publishedAt' => 'publication_date',
        ];
    }
}
