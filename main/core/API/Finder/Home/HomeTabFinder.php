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
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder.home_tab")
 * @DI\Tag("claroline.finder")
 */
class HomeTabFinder extends AbstractFinder
{
    public function getClass()
    {
        return HomeTab::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        $qb->leftJoin('obj.homeTabConfigs', 'config');

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'user':
                    $roles = $this->om->find(User::class, $filterValue)->getRoles();

                    $qb->leftJoin('obj.user', 'u');

                    $expr = [];

                    if (!in_array('ROLE_ADMIN', $roles)) {
                        $subQuery =
                          "
                            SELECT tab from Claroline\CoreBundle\Entity\Tab\HomeTab tab
                            JOIN tab.homeTabConfigs htc
                            JOIN htc.roles role
                            JOIN role.users user

                            WHERE (user.uuid = :userId OR user.id = :userId)
                            AND tab.type = :adminDesktop
                            AND htc.locked = true
                          ";

                        $subQuery2 =
                          "
                            SELECT tab2 from Claroline\CoreBundle\Entity\Tab\HomeTab tab2
                            JOIN tab2.homeTabConfigs htc2
                            LEFT JOIN htc2.roles role2
                            WHERE role2.id IS NULL
                            AND tab2.type = :adminDesktop
                            AND htc2.locked = true
                          ";

                        $expr[] = $qb->expr()->orX(
                            $qb->expr()->in('obj', $subQuery),
                            $qb->expr()->in('obj', $subQuery2)
                        );
                        $qb->setParameter('adminDesktop', HomeTab::TYPE_ADMIN_DESKTOP);
                    } else {
                        $expr[] = $qb->expr()->andX(
                          $qb->expr()->eq('obj.type', ':adminDesktop')
                        );
                        $qb->setParameter('adminDesktop', HomeTab::TYPE_ADMIN_DESKTOP);
                    }

                    $expr[] = $qb->expr()->orX(
                      $qb->expr()->eq('u.id', ':userId'),
                      $qb->expr()->eq('u.uuid', ':userId')
                    );

                    $qb->andWhere($qb->expr()->orX(...$expr));

                    $qb->setParameter('userId', $filterValue);
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

        $qb->orderBy('config.tabOrder', 'ASC');

        return $qb;
    }
}
