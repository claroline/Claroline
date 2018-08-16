<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\Home;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\AppBundle\API\Finder\FinderTrait;
use Claroline\CoreBundle\Entity\Widget\WidgetContainer;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.widget_container")
 * @DI\Tag("claroline.finder")
 */
class WidgetContainerFinder extends AbstractFinder
{
    use FinderTrait;

    public function getClass()
    {
        return WidgetContainer::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'homeTab':
                  $qb->leftJoin('obj.homeTab', 't');
                  $qb->andWhere('t.uuid = :homeTab');
                  $qb->setParameter('homeTab', $filterValue);
            }
        }

        return $qb;
    }
}
