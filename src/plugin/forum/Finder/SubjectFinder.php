<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Doctrine\ORM\QueryBuilder;

class SubjectFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Subject::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $creatorJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'forum':
                    $qb->leftJoin('obj.forum', 'forum');
                    $qb->andWhere("forum.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'createdAfter':
                    $qb->andWhere("obj.creationDate >= :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'createdBefore':
                    $qb->andWhere("obj.creationDate <= :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'creator':
                    if (!$creatorJoin) {
                        $qb->leftJoin('obj.creator', 'creator');
                        $creatorJoin = true;
                    }

                    $qb->andWhere("creator.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'moderation':
                    if ($filterValue) {
                        $qb->andWhere($qb->expr()->orX(
                            $qb->expr()->eq('obj.moderation', ':prior_once'),
                            $qb->expr()->eq('obj.moderation', ':prior_all')
                        ));

                        $qb->setParameter('prior_once', Forum::VALIDATE_PRIOR_ONCE);
                        $qb->setParameter('prior_all', Forum::VALIDATE_PRIOR_ALL);
                    } else {
                        $qb->andWhere($qb->expr()->orX(
                            $qb->expr()->eq('obj.moderation', ':filter_none'),
                            $qb->expr()->isNull('obj.moderation')
                        ));

                        $qb->setParameter('filter_none', Forum::VALIDATE_NONE);
                    }
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        // manages custom sort properties
        if (!empty($sortBy)) {
            switch ($sortBy['property']) {
                case 'sticked':
                    $qb->addOrderBy('obj.sticked', 1 === $sortBy['direction'] ? 'ASC' : 'DESC');
                    $qb->addOrderBy('obj.title', 'ASC');
                    break;
                case 'meta.messages':
                    $qb->select('obj, count(msg) AS HIDDEN countMsg');
                    $qb->leftJoin('obj.messages', 'msg');
                    $qb->groupBy('obj');
                    $qb->orderBy('countMsg', 1 === $sortBy['direction'] ? 'ASC' : 'DESC');
                    break;
                case 'creator':
                    if (!$creatorJoin) {
                        $qb->leftJoin('obj.creator', 'creator');
                    }

                    $qb->orderBy('creator.username', 1 === $sortBy['direction'] ? 'ASC' : 'DESC');
                    break;
                case 'lastMessage':
                    $qb->select('obj, MAX(lm.creationDate) AS HIDDEN lastMessageCreated');
                    $qb->leftJoin('obj.messages', 'lm');
                    $qb->groupBy('obj');
                    $qb->orderBy('lm.moderation', 'ASC');
                    $qb->orderBy('lastMessageCreated', 1 === $sortBy['direction'] ? 'ASC' : 'DESC');
                    break;
            }
        }

        return $qb;
    }

    protected function getExtraFieldMapping(): array
    {
        return [
            'meta.creator' => 'creator',
        ];
    }

    public function getFilters(): array
    {
        return [
            'forum' => [
                'type' => ['integer', 'string'],
                'description' => 'The parent forum id (int) or uuid (string)',
            ],
            'title' => [
                'type' => 'string',
                'description' => 'The subject content',
            ],
            'creationDate' => [
                'type' => 'datetime',
                'description' => 'The creation date',
            ],
            'updated' => [
                'type' => 'datetime',
                'description' => 'The last update date',
            ],
            'author' => [
                'type' => 'string',
                'description' => 'the author name',
            ],
            'sticked' => [
                'type' => 'boolean',
                'description' => 'is the subject sticked',
            ],
            'closed' => [
                'type' => 'boolean',
                'description' => 'is the subject closed',
            ],
            'viewCount' => [
                'type' => 'integer',
                'description' => 'The number of views',
            ],
        ];
    }
}
