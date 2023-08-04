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
use Claroline\CoreBundle\Security\PlatformRoles;
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

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $isAdmin = false;
        if ($this->tokenStorage->getToken()) {
            $isAdmin = $this->authChecker->isGranted('ROLE_ADMIN');
        }

        // if not admin, don't list platform_admin role, for security purpose
        if (!$isAdmin) {
            $qb->andWhere('obj.name != :roleAdmin');
            $qb->setParameter('roleAdmin', PlatformRoles::ADMIN);
        }

        $groupJoin = false;
        $workspaceJoin = false;

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
                case 'users':
                    $qb->leftJoin('obj.users', 'ru', 'WITH', 'ru.uuid IN (:userIds)');

                    $qb->andWhere('(ru IS NOT NULL OR EXISTS (
                        SELECT u2.id 
                        FROM Claroline\CoreBundle\Entity\User AS u2
                        JOIN u2.groups AS g2
                        JOIN g2.roles AS r2
                        WHERE u2.uuid IN (:userIds)
                          AND r2.id = obj.id
                    ))');

                    $qb->setParameter('userIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'group':
                case 'groups':
                    if (!$groupJoin) {
                        $qb->leftJoin('obj.groups', 'g');
                        $groupJoin = true;
                    }

                    $qb->andWhere('g.uuid IN (:groupIds)');
                    $qb->setParameter('groupIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'workspace':
                case 'workspaces':
                    if (!$workspaceJoin) {
                        $qb->leftJoin('obj.workspace', 'w');
                        $workspaceJoin = true;
                    }

                    $qb->andWhere('w.uuid IN (:workspaceIds)');
                    $qb->setParameter('workspaceIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'workspaceConfigurable':
                    if (!$workspaceJoin) {
                        $qb->leftJoin('obj.workspace', 'w');
                        $workspaceJoin = true;
                    }

                    $qb->andWhere('w.uuid IN (:workspaceIds)');
                    $qb->setParameter('workspaceIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    $qb->orWhere('obj.name LIKE :roleAnonymous');
                    $qb->orWhere('obj.name LIKE :roleUser');
                    $qb->setParameter('roleAnonymous', PlatformRoles::ANONYMOUS);
                    $qb->setParameter('roleUser', PlatformRoles::USER);
                    break;
                case 'grantable':
                    if (!$isAdmin && $this->tokenStorage->getToken()) {
                        $qb->join('obj.users', 'cu');
                        $qb->andWhere('cu.id = :currentUserId');
                        $qb->setParameter('currentUserId', $this->tokenStorage->getToken()->getUser()->getId());
                    }
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
