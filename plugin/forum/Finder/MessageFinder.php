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
use Doctrine\ORM\QueryBuilder;

class MessageFinder extends AbstractFinder
{
    public function getClass()
    {
        return 'Claroline\ForumBundle\Entity\Message';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'subject':
                    $qb->join('obj.subject', 'subject');
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->like('subject.id', ':'.$filterName),
                        $qb->expr()->like('subject.uuid', ':'.$filterName)
                    ));
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'subject.title':
                    $qb->join('obj.subject', 'subject');
                    $qb->andWhere('UPPER(subject.title) LIKE :title');
                    $qb->setParameter('title', '%'.strtoupper($filterValue).'%');
                    break;
                case 'parent':
                    if (empty($filterValue)) {
                        $qb->andWhere('obj.parent IS NULL');
                    } else {
                        $qb->join('obj.parent', 'parent');
                        $qb->andWhere($qb->expr()->orX(
                            $qb->expr()->like('parent.id', ':'.$filterName),
                            $qb->expr()->like('parent.uuid', ':'.$filterName)
                        ));
                        $qb->setParameter($filterName, $filterValue);
                    }
                    break;
                case 'forum':
                    $qb->join('obj.subject', 'sf');
                    $qb->join('sf.forum', 'forum');
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->like('forum.id', ':'.$filterName),
                        $qb->expr()->like('forum.uuid', ':'.$filterName)
                    ));
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'creator':
                    $qb->join('obj.creator', 'creator');
                    $qb->andWhere("creator.username LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.$filterValue.'%');
                    break;
                case 'creatorId':
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
                    $qb->join('obj.subject', 'sf');
                    $qb->join('sf.forum', 'forum');
                    $qb->join('forum.resourceNode', 'node');
                    $qb->join('node.workspace', 'w');
                    $qb->andWhere("w.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'anonymous':
                    $qb->join('obj.subject', 'sf');
                    $qb->join('sf.forum', 'forum');
                    $qb->join('forum.resourceNode', 'node');
                    $qb->join('node.rights', 'rights');
                    $qb->join('rights.role', 'role');
                    $qb->andWhere("role.name = 'ROLE_ANONYMOUS'");
                    $qb->andWhere('BIT_AND(rights.mask, 1) = 1');
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
    }

    public function getFilters()
    {
        return [
            'subject' => [
                'type' => ['integer', 'string'],
                'description' => 'The parent subject id (int) or uuid (string)',
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
            'author' => [
                'type' => 'string',
                'description' => 'the author name',
            ],
        ];
    }
}
