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

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        // force non archived workspaces only if not explicitly requested
        if (!array_key_exists('archived', $searches)) {
            $searches['archived'] = false;
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'administrated':
                    if ('cli' !== php_sapi_name() && !$this->authChecker->isGranted('ROLE_ADMIN')) {
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

                        /** @var User $currentUser */
                        $currentUser = $this->tokenStorage->getToken()->getUser();

                        $qb->setParameter('uaId', $currentUser instanceof User ? $currentUser->getId() : null);
                        $qb->setParameter('cId', $currentUser instanceof User ? $currentUser->getId() : null);
                        $qb->setParameter('ruId', $currentUser instanceof User ? $currentUser->getId() : null);
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
                case 'organizations':
                    $qb->leftJoin('obj.organizations', 'o');
                    $qb->andWhere('o.uuid IN (:organizationIds)');
                    $qb->setParameter('organizationIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'roles':
                    $qb->leftJoin('obj.roles', 'r');
                    $qb->andWhere('r.name IN (:roleNames)');
                    $qb->setParameter('roleNames', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }

    protected function getExtraFieldMapping(): array
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

            '$defaults' => [],
        ];
    }
}
