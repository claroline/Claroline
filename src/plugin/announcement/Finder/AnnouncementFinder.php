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

/**
 * @deprecated
 */
class AnnouncementFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Announcement::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], ?array $sortBy = null, ?int $page = 0, ?int $limit = -1): QueryBuilder
    {
        $workspaceJoin = false;
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'creator':
                    $qb->leftJoin('obj.creator', 'creator');
                    $qb->andWhere("creator.username LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.$filterValue.'%');
                    break;
                case 'workspace':
                    if (!$workspaceJoin) {
                        $qb->join('obj.workspace', 'w');

                        $workspaceJoin = true;
                    }

                    $qb->andWhere("w.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'archived':
                    if (!$workspaceJoin) {
                        $qb->join('obj.workspace', 'w');

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

                        $qb->andWhere('(obj.visibleFrom >= :fromDate OR obj.visibleFrom IS NULL)');
                        $qb->andWhere('(obj.visibleUntil <= :untilDate OR obj.visibleUntil IS NULL)');
                        $qb->andWhere('obj.visible = true');

                        $qb->setParameter('fromDate', $now);
                        $qb->setParameter('untilDate', $now);
                    }
                    break;
                case 'roles':
                    $qb->leftJoin('obj.roles', 'r');
                    $qb->andWhere('r IS NULL OR r.name IN (:roles)');
                    $qb->setParameter('roles', $filterValue);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }

    protected function getExtraFieldMapping(): array
    {
        return [
            'meta.publishedAt' => 'publication_date',
            'meta.author' => 'announcer',
        ];
    }
}
