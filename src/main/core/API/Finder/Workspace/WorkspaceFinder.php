<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\Workspace;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class WorkspaceFinder extends AbstractFinder
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
        return Workspace::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        // force non archived workspaces only if not explicitly requested
        if (!array_key_exists('archived', $searches)) {
            $searches['archived'] = false;
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'orphan':
                    if ($filterValue) {
                        $qb->andWhere('obj.personal = true');
                        $qb->leftJoin('obj.personalUser', 'ps');
                        $qb->andWhere('ps.isRemoved = true');
                    }
                    break;
                case 'sameOrganization':
                    $currentUser = $this->tokenStorage->getToken()->getUser();

                    if ($currentUser instanceof User) {
                        $qb->leftJoin('obj.organizations', 'uo');
                        $qb->leftJoin('uo.userOrganizationReferences', 'ua');

                        $qb->andWhere($qb->expr()->orX(
                            $qb->expr()->eq('ua.user', ':userOganizationId')
                        ));

                        $qb->setParameter('userOganizationId', $currentUser->getId());
                    }

                    break;
                case 'administrated':
                    if ('cli' !== php_sapi_name() && !$this->authChecker->isGranted('ROLE_ADMIN') && !$this->authChecker->isGranted('ROLE_ANONYMOUS')) {
                        /** @var User $currentUser */
                        $currentUser = $this->tokenStorage->getToken()->getUser();
                        $qb->leftJoin('obj.organizations', 'uo');
                        $qb->leftJoin('uo.administrators', 'ua');
                        $qb->leftJoin('obj.creator', 'creator');
                        $qb->leftJoin('obj.roles', 'r');
                        $qb->leftJoin('r.users', 'ru');
                        $qb->andWhere($qb->expr()->orX(
                            $qb->expr()->eq('ua.id', ':uaId'),
                            $qb->expr()->eq('creator.id', ':cId'),
                            $qb->expr()->andX(
                                $qb->expr()->eq('r.name', "CONCAT('ROLE_WS_MANAGER_', obj.uuid)"),
                                $qb->expr()->eq('ru.id', ':ruId')
                            )
                        ));
                        $qb->setParameter('uaId', $currentUser->getId());
                        $qb->setParameter('cId', $currentUser->getId());
                        $qb->setParameter('ruId', $currentUser->getId());
                    }
                    break;
                case 'createdAfter':
                    $qb->andWhere("obj.createdAt >= :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'createdBefore':
                    $qb->andWhere("obj.createdAt <= :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'organization':
                    $qb->leftJoin('obj.organizations', 'o');
                    $qb->andWhere('o.uuid IN (:organizationIds)');
                    $qb->setParameter('organizationIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case '_user':
                    $qb->leftJoin('obj.roles', 'r');
                    $qb->leftJoin('r.users', 'ru');
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->like('ru.id', ':_userId'),
                        $qb->expr()->like('ru.uuid', ':_userUuid')
                    ));
                    $qb->andWhere('r.name != :roleUser');
                    $qb->setParameter('_userId', $filterValue);
                    $qb->setParameter('_userUuid', $filterValue);
                    $qb->setParameter('roleUser', 'ROLE_USER');

                    break;
                case '_group':
                    $qb->leftJoin('obj.roles', 'r');
                    $qb->leftJoin('r.groups', 'rg');
                    $qb->leftJoin('rg.users', 'rgu');
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->like('rgu.id', ':_groupUserId'),
                        $qb->expr()->like('rgu.uuid', ':_groupUserUuid')
                    ));
                    $qb->andWhere('r.name != :roleUser');
                    $qb->setParameter('_groupUserId', $filterValue);
                    $qb->setParameter('_groupUserUuid', $filterValue);
                    $qb->setParameter('roleUser', 'ROLE_USER');
                    break;
                case 'user':
                    $byUserSearch = $byGroupSearch = $searches;
                    $byUserSearch['_user'] = $filterValue;
                    $byGroupSearch['_group'] = $filterValue;
                    unset($byUserSearch['user']);
                    unset($byGroupSearch['user']);

                    return $this->union($byUserSearch, $byGroupSearch, $options, $sortBy);
                    break;
                    //use this with the 'user' property
                case 'isManager':
                    if ($filterValue) {
                        $qb->andWhere('r.name like :ROLE_WS_MANAGER');
                        $qb->setParameter('ROLE_WS_MANAGER', 'ROLE_WS_MANAGER%');
                    }
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }

    public function getExtraFieldMapping()
    {
        return [
          'meta.personal' => 'personal',
          'meta.model' => 'model',
        ];
    }

    public function getFilters(): array
    {
        return [
            'administrated' => [
                'type' => 'boolean',
                'description' => 'The the current user administrate the organization of the workspace',
            ],

            'sameOrganization' => [
                'type' => 'boolean',
                'description' => 'Workspace and current user share the same organization',
            ],

            'createdBefore' => [
                'type' => 'date',
                'description' => 'Workspace created after',
            ],

            'createdAfter' => [
                'type' => 'date',
                'description' => 'Workspace created before',
            ],

            'user' => [
                'type' => 'integer',
                'description' => 'The user id/uuid. Check if the user is registered',
            ],

            'isManager' => [
                'type' => 'boolean',
                'description' => 'Requires the user filter. Check if the user is the manager aswell',
            ],

            '$defaults' => [],
        ];
    }
}
