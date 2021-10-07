<?php

namespace Icap\BlogBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Doctrine\ORM\QueryBuilder;
use Icap\BlogBundle\Entity\Post;

class PostFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Post::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $qb->leftJoin('obj.blog', 'b');
        $qb->leftJoin('b.resourceNode', 'node');

        $workspaceJoin = false;
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'published':
                    if ($filterValue) {
                        $qb->andWhere('node.published = :published');
                        $qb->andWhere('node.active = :active');
                        $qb->andWhere('obj.status = :status');
                        $qb->andWhere('obj.publicationDate <= :endOfDay');
                        $qb->setParameter('published', true);
                        $qb->setParameter('status', true);
                        $qb->setParameter('active', true);
                        $qb->setParameter('endOfDay', new \DateTime('tomorrow'));
                    } else {
                        $qb->andWhere('node.published = :published');
                        $qb->andWhere('obj.status = :status');
                        $qb->setParameter('published', false);
                        $qb->setParameter('status', false);
                    }
                    break;
                case 'author':
                    $qb->innerJoin('obj.creator', 'creator');
                    $qb->andWhere("
                        UPPER(obj.author) LIKE :{$filterName}
                        OR UPPER(creator.firstName) LIKE :{$filterName}
                        OR UPPER(creator.lastName) LIKE :{$filterName}
                        OR UPPER(CONCAT(CONCAT(creator.firstName, ' '), creator.lastName)) LIKE :{$filterName}
                        OR UPPER(CONCAT(CONCAT(creator.lastName, ' '), creator.firstName)) LIKE :{$filterName}
                    ");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    break;
                case 'publicationDate':
                    $date = DateNormalizer::denormalize($filterValue);

                    $beginOfDay = clone $date;
                    $beginOfDay->modify('today');
                    $endOfDay = clone $beginOfDay;
                    $endOfDay->modify('tomorrow');
                    $endOfDay->modify('1 second ago');

                    $qb->andWhere("obj.{$filterName} >= :beginOfDay");
                    $qb->andWhere("obj.{$filterName} <= :endOfDay");
                    $qb->setParameter(':beginOfDay', $beginOfDay);
                    $qb->setParameter(':endOfDay', $endOfDay);
                    break;
                case 'fromDate':
                    $date = DateNormalizer::denormalize($filterValue);
                    $beginOfDay = clone $date;
                    $beginOfDay->modify('today');

                    $qb->andWhere('obj.publicationDate >= :beginOfDay');
                    $qb->setParameter(':beginOfDay', $beginOfDay);
                    break;
                case 'toDate':
                    $date = DateNormalizer::denormalize($filterValue);
                    $beginOfDay = clone $date;
                    $beginOfDay->modify('today');
                    $endOfDay = clone $beginOfDay;
                    $endOfDay->modify('tomorrow');
                    $endOfDay->modify('1 second ago');

                    $qb->andWhere('obj.publicationDate <= :endOfDay');
                    $qb->setParameter(':endOfDay', $endOfDay);
                    break;
                case 'tag':
                    $qb->andWhere('obj.uuid IN (
                        SELECT to.objectId
                        FROM Claroline\\TagBundle\\Entity\\TaggedObject to
                        INNER JOIN to.tag t
                        WHERE UPPER(t.name) = :tagFilter
                    )');
                    $qb->setParameter('tagFilter', strtoupper($filterValue));
                    break;
                case 'roles':
                    $qb->leftJoin('node.rights', 'rights');
                    $qb->join('rights.role', 'rightsr');
                    $qb->andWhere('rightsr.name IN (:roles)');
                    $qb->andWhere('BIT_AND(rights.mask, 1) = 1');
                    $qb->setParameter('roles', $filterValue);
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
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        //pinned always first
        $qb->addOrderBy('obj.pinned', 'DESC');
        //and then custom sort
        if (!empty($sortBy)) {
            $qb->addOrderBy('obj.'.$sortBy['property'], 1 === $sortBy['direction'] ? 'ASC' : 'DESC');
        }

        return $qb;
    }
}
