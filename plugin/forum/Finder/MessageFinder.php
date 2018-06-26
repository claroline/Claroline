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

use Claroline\AppBundle\API\Finder\FinderTrait;
use Claroline\AppBundle\API\FinderInterface;
use Claroline\ForumBundle\Entity\Forum;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.forum_message")
 * @DI\Tag("claroline.finder")
 */
class MessageFinder implements FinderInterface
{
    use FinderTrait;

    public function getClass()
    {
        return 'Claroline\ForumBundle\Entity\Message';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
              case 'subject':
                $qb->leftJoin('obj.subject', 'subject');
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('subject.id', ':'.$filterName),
                    $qb->expr()->eq('subject.uuid', ':'.$filterName)
                ));
                $qb->setParameter($filterName, $filterValue);
                break;
              case 'parent':
                if (empty($filterValue)) {
                    $qb->andWhere('obj.parent IS NULL');
                } else {
                    $qb->leftJoin('obj.parent', 'parent');
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->eq('parent.id', ':'.$filterName),
                        $qb->expr()->eq('parent.uuid', ':'.$filterName)
                    ));
                    $qb->setParameter($filterName, $filterValue);
                }
                break;
              case 'forum':
                $qb->leftJoin('obj.subject', 'sf');
                $qb->leftJoin('sf.forum', 'forum');
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('forum.id', ':'.$filterName),
                    $qb->expr()->eq('forum.uuid', ':'.$filterName)
                ));
                $qb->setParameter($filterName, $filterValue);
                break;
              case 'creator':
                $qb->leftJoin('obj.creator', 'creator');
                $qb->andWhere("creator.username LIKE :{$filterName}");
                $qb->setParameter($filterName, '%'.$filterValue.'%');
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
                    $qb->andWhere('obj.moderation = :filter_none');
                    $qb->setParameter('filter_none', Forum::VALIDATE_NONE);
                }
                break;
              default:
                $this->setDefaults($qb, $filterName, $filterValue);
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
