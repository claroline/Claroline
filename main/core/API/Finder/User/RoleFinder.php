<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\User;

use Claroline\AppBundle\API\FinderInterface;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.api.finder.role")
 * @DI\Tag("claroline.finder")
 */
class RoleFinder implements FinderInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * WorkspaceFinder constructor.
     *
     * @DI\InjectParams({
     *     "authChecker"  = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param AuthorizationCheckerInterface $authChecker
     * @param TokenStorageInterface         $tokenStorage
     */
    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Role';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
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
                        case 'custom':
                            $filterValue = Role::CUSTOM_ROLE;
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

              default:
                if (is_bool($filterValue)) {
                    $qb->andWhere("obj.{$filterName} = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                } else {
                    if (is_int($filterValue)) {
                        $qb->andWhere("obj.{$filterName} = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    } else {
                        $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                        $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    }
                }
            }
        }

        return $qb;
    }
}
