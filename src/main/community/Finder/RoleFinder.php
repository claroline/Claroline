<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RoleFinder extends AbstractFinder
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getClass(): string
    {
        return Role::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        if ($this->tokenStorage->getToken()) {
            $isAdmin = $this->authChecker->isGranted('ROLE_ADMIN');
        } else {
            $isAdmin = true;
        }

        //if not admin doesnt list platform_admin role, for security purpose
        if (!$isAdmin) {
            $qb->andWhere("obj.name != 'ROLE_ADMIN'");
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'type':
                    switch ($filterValue) {
                        case 'workspace':
                            $filterValue = Role::WS_ROLE;
                            break;
                        case 'user':
                            $filterValue = Role::USER_ROLE;
                            break;
                        case 'platform':
                            $filterValue = Role::PLATFORM_ROLE;
                            break;
                    }
                    $qb->andWhere("obj.{$filterName} = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'user':
                    $qb->leftJoin('obj.users', 'ru');
                    $qb->andWhere('ru.uuid IN (:userIds)');
                    $qb->setParameter('userIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'group':
                    $qb->leftJoin('obj.groups', 'g');
                    $qb->andWhere('g.uuid IN (:groupIds)');
                    $qb->setParameter('groupIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'workspace':
                    $qb->leftJoin('obj.workspace', 'w');
                    $qb->andWhere('w.uuid IN (:workspaceIds)');
                    $qb->setParameter('workspaceIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'workspaceConfigurable':
                    $qb->leftJoin('obj.workspace', 'w');
                    $qb->andWhere('w.uuid IN (:workspaceIds)');
                    $qb->setParameter('workspaceIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    $qb->orWhere("obj.name LIKE 'ROLE_ANONYMOUS'");
                    $qb->orWhere("obj.name LIKE 'ROLE_USER'");
                    break;
                case 'grantable':
                    if (!$isAdmin && $this->tokenStorage->getToken()) {
                        $qb->join('obj.users', 'cu');
                        $qb->andWhere('cu.id = :currentUserId');
                        $qb->setParameter('currentUserId', $this->tokenStorage->getToken()->getUser()->getId());
                    }
                    break;
                case 'roleNames':
                    $qb->orWhere('obj.name IN (:roleNames)');
                    $qb->setParameter('roleNames', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
