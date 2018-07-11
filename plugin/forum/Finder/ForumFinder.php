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
use Claroline\AppBundle\API\Finder\FinderTrait;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.forum")
 * @DI\Tag("claroline.finder")
 */
class ForumFinder extends AbstractFinder
{
    use FinderTrait;

    public function getClass()
    {
        return 'Claroline\ForumBundle\Entity\Forum';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
              default:
                $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }

    public function getFilters()
    {
        return [
          'validationMode' => [
            'type' => 'integer',
            'description' => 'The forum validation mode',
          ],
          'maxComment' => [
            'type' => 'integer',
            'description' => 'The max amount of sub comments per messages',
          ],
        ];
    }
}
