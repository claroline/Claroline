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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.api.finder.home_tab")
 * @DI\Tag("claroline.finder")
 */
class HomeTabFinder extends AbstractFinder
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * WorkspaceFinder constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getClass()
    {
        return HomeTab::class;
    }

    public function configureQueryBuilder(
      QueryBuilder $qb,
      array $searches = [],
      array $sortBy = null,
      array $options = ['count' => false, 'page' => 0, 'limit' => -1]
    ) {
        $qb = $this->om->createQueryBuilder();
        $qb->select($options['count'] ? 'COUNT(obj)' : 'obj')->from($this->getClass(), 'obj');
        $qb->leftJoin('obj.homeTabConfigs', 'config');

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'user':
                    $user = $this->om->find(User::class, $filterValue);
                    $roles = $user ? $user->getRoles() : [];

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
                            WHERE tab2.type = :adminDesktop
                            AND htc2.locked = true
                            GROUP BY tab2.id
                            HAVING COUNT(role2.id) = 0
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
                      $qb->expr()->like('u.id', ':userId'),
                      $qb->expr()->like('u.uuid', ':userId')
                    );

                    $qb->andWhere($qb->expr()->orX(...$expr));

                    $qb->setParameter('userId', $filterValue);
                    break;
                case 'workspace':
                    $roleNames = array_map(function ($role) {
                        return $role->getRole();
                    }, $this->tokenStorage->getToken()->getRoles());

                    if (in_array('ROLE_ADMIN', $roleNames) || in_array('ROLE_MANAGER_'.$filterValue, $roleNames)) {
                        $qb->leftJoin('obj.workspace', 'w');
                        $qb->andWhere("w.uuid = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    } else {
                        $freeSearch = $workspaceSearch = $searches;
                        $freeSearch['_workspace_free'] = $filterValue;
                        $workspaceSearch['_workspace_roles'] = $filterValue;
                        unset($workspaceSearch['workspace']);
                        unset($freeSearch['workspace']);

                        return $this->union($freeSearch, $workspaceSearch, $options, $sortBy);
                    }

                    break;
                case '_workspace_free':
                    $qb->join('obj.workspace', '_wf');
                    $qb->leftJoin('config.roles', '_rr2');
                    $qb->andWhere("_wf.uuid = :{$filterName}");
                    $qb->groupBy('obj.id');
                    $qb->having('COUNT(_rr2.id) = 0');
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case '_workspace_roles':
                    $roleNames = array_map(function ($role) {
                        return $role->getRole();
                    }, $this->tokenStorage->getToken()->getRoles());
                    $qb->join('obj.workspace', '_wr');
                    $qb->join('config.roles', '_rr');
                    $qb->andWhere("_wr.uuid = :{$filterName}");
                    $qb->andWhere($qb->expr()->in('_rr.name', ':roleNames'));
                    $qb->setParameter($filterName, $filterValue);
                    $qb->setParameter('roleNames', $roleNames);
                    break;
                default:
                  $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        if (!array_key_exists('_workspace_free', $searches) && !array_key_exists('_workspace_roles', $searches)) {
            $qb->orderBy('config.tabOrder', 'ASC');
        }

        return $qb;
    }

    public function getFilters()
    {
        return [
            '$defaults' => [],
        ];
    }
}
