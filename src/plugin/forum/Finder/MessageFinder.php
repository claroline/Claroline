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
use Claroline\ForumBundle\Entity\Message;
use Doctrine\ORM\QueryBuilder;

class MessageFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Message::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $workspaceJoin = false;

        $qb->join('obj.subject', 'subject');
        $qb->join('subject.forum', 'forum');
        $qb->join('forum.resourceNode', 'node');

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'published':
                    $qb->andWhere('node.published = :published');
                    $qb->andWhere('node.active = :active');
                    $qb->setParameter('published', $filterValue);
                    $qb->setParameter('active', $filterValue);
                    break;
                case 'subject':
                    $qb->andWhere("subject.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'parent':
                    if (empty($filterValue)) {
                        $qb->andWhere('obj.parent IS NULL');
                    } else {
                        $qb->join('obj.parent', 'parent');
                        $qb->andWhere("parent.uuid = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    }
                    break;
                case 'forum':
                    $qb->andWhere("forum.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'creator':
                    $qb->join('obj.creator', 'creator');
                    $qb->andWhere("creator.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'createdAfter':
                    $qb->andWhere("obj.creationDate >= :{$filterName}");
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
                case 'roles':
                    $qb->leftJoin('node.rights', 'rights');
                    $qb->join('rights.role', 'rightsr');
                    $qb->andWhere('rightsr.name IN (:roles)');
                    $qb->andWhere('BIT_AND(rights.mask, 1) = 1');
                    $qb->setParameter('roles', $filterValue);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'meta.created':
                    $qb->orderBy('obj.creationDate', $sortByDirection);
                    break;
                case 'subject.title':
                    $qb->orderBy('subject.title', $sortByDirection);
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
            'subject' => [
                'type' => ['string'],
                'description' => 'The parent subject uuid',
            ],
            'parent' => [
                'type' => ['integer', 'string'],
                'description' => 'The parent message id (int) or uuid (string)',
            ],
            'flagged' => [
                'type' => 'boolean',
                'description' => 'If the message is visible',
            ],
            'moderation' => [
                'type' => 'boolean',
                'description' => 'If the message is waiting for a moderator',
            ],
            'content' => [
                'type' => 'string',
                'description' => 'The message content',
            ],
            'creationDate' => [
                'type' => 'datetime',
                'description' => 'The creation date',
            ],
            'updated' => [
                'type' => 'datetime',
                'description' => 'The last update date',
            ],
        ];
    }
}
