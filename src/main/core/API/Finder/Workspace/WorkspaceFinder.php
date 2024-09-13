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
    public function __construct(
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getClass(): string
    {
        return Workspace::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, ?int $page = 0, ?int $limit = -1): QueryBuilder
    {
        $organizationJoin = false;

        // force non archived workspaces only if not explicitly requested
        if (!array_key_exists('archived', $searches)) {
            $searches['archived'] = false;
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'administrated':
                    if ('cli' !== php_sapi_name() && !$this->authChecker->isGranted('ROLE_ADMIN')) {
                        if (!$organizationJoin) {
                            $qb->leftJoin('obj.organizations', 'o');
                            $organizationJoin = true;
                        }

                        $qb->leftJoin('o.userOrganizationReferences', 'uo');
                        $qb->leftJoin('obj.creator', 'creator');
                        $qb->leftJoin('obj.roles', 'r');
                        $qb->leftJoin('r.users', 'ru');
                        $qb->andWhere($qb->expr()->orX(
                            $qb->expr()->andX(
                                $qb->expr()->eq('uo.user', ':uaId'),
                                $qb->expr()->eq('uo.manager', '1')
                            ),
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
                case 'organization':
                case 'organizations':
                    if (!$organizationJoin) {
                        $qb->leftJoin('obj.organizations', 'o');
                        $organizationJoin = true;
                    }

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
            'meta.description' => 'description',
        ];
    }

    public function getFilters(): array
    {
        return [
            'administrated' => [
                'type' => 'boolean',
                'description' => 'The current user administrate the organization of the workspace',
            ],

            'user' => [
                'type' => 'integer',
                'description' => 'The user id/uuid. Check if the user is registered',
            ],

            '$defaults' => [],
        ];
    }
}
