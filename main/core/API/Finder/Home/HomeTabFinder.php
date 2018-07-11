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
use Claroline\CoreBundle\Entity\Home\HomeTab;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.home_tab")
 * @DI\Tag("claroline.finder")
 */
class HomeTabFinder extends AbstractFinder
{
    use FinderTrait;

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Home\HomeTab';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'user':
                    $qb->leftJoin('obj.user', 'u');
                    $qb->andWhere($qb->expr()->orX(
                      $qb->expr()->eq('u.id', ':userId'),
                      $qb->expr()->eq('u.uuid', ':userId')
                    ));
                    $qb->orWhere('obj.type = :desktopType');
                    $qb->setParameter('userId', $filterValue);
                    $qb->setParameter('desktopType', HomeTab::TYPE_ADMIN_DESKTOP);
                    break;
                case 'workspace':
                    $qb->leftJoin('obj.workspace', 'w');
                    $qb->andWhere("w.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);

                    break;
                default:
                  $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
