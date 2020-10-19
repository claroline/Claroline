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
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class HomeTabFinder extends AbstractFinder
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * WorkspaceFinder constructor.
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

        // only grab tabs accessible by user
        $roleNames = $this->tokenStorage->getToken()->getRoleNames();

        $isAdmin = in_array('ROLE_ADMIN', $roleNames) || (isset($searches['workspace']) && in_array('ROLE_MANAGER_'.$searches['workspace'], $roleNames));
        if (!$isAdmin) {
            // only get visible tabs for non admin
            $qb->andWhere('config.visible = true');

            // only get tabs visible by the current user roles
            if (!isset($searches['type']) || HomeTab::TYPE_DESKTOP !== $searches['type']) {
                // no need to check roles for DESKTOP tabs because it's directly linked to the user
                $qb->leftJoin('config.roles', 'r');
                $qb->andWhere('(r.id IS NULL OR r.name IN (:roles))');
                $qb->setParameter('roles', $roleNames);
            }
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'type':
                    $qb->andWhere("obj.type = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    if (HomeTab::TYPE_DESKTOP === $filterValue) {
                        // only get DESKTOP tabs for the current user
                        $qb->leftJoin('obj.user', 'u');
                        $qb->andWhere('u.id = :userId');
                        $qb->setParameter('userId', $this->tokenStorage->getToken()->getUser()->getId());
                    }

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

    public function getFilters()
    {
        return [
            '$defaults' => [],
        ];
    }
}
