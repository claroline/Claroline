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
 * @DI\Service("claroline.api.finder.forum_subject")
 * @DI\Tag("claroline.finder")
 */
class SubjectFinder implements FinderInterface
{
    use FinderTrait;

    public function getClass()
    {
        return 'Claroline\ForumBundle\Entity\Subject';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
              case 'forum':
                $qb->leftJoin('obj.forum', 'forum');
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('forum.id', ':'.$filterName),
                    $qb->expr()->eq('forum.uuid', ':'.$filterName)
                ));
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
                $qb->leftJoin('obj.creator', 'creator');
                $qb->andWhere("creator.username LIKE :{$filterName}");
                $qb->setParameter($filterName, '%'.$filterValue.'%');
                break;
              case 'tags':
                $qb->andWhere("obj.uuid IN (
                  SELECT to.objectId
                  FROM Claroline\TagBundle\Entity\TaggedObject to
                  LEFT JOIN to.tag t
                  WHERE UPPER(t.name) LIKE :tagFilter
                )");
                $qb->setParameter('tagFilter', '%'.strtoupper($filterValue).'%');
                break;
              default:
                $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        // manages custom sort properties
        if (!empty($sortBy)) {
            switch ($sortBy['property']) {
                case 'meta.messages':
                    $qb->select('obj, count(msg) AS HIDDEN countMsg');
                    $qb->leftJoin('obj.messages', 'msg');
                    $qb->groupBy('obj');
                    $qb->orderBy('countMsg', 1 === $sortBy['direction'] ? 'ASC' : 'DESC');
                    break;
                case 'creator':
                    $qb->leftJoin('obj.creator', 'sortCreator');
                    $qb->orderBy('sortCreator.username', 1 === $sortBy['direction'] ? 'ASC' : 'DESC');
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

    public function getFilters()
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
