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
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.api.finder.group")
 * @DI\Tag("claroline.finder")
 */
class GroupFinder implements FinderInterface
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
        return 'Claroline\CoreBundle\Entity\Group';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        if (!$this->authChecker->isGranted('ROLE_ADMIN')) {
            /** @var User $currentUser */
            $currentUser = $this->tokenStorage->getToken()->getUser();
            $qb->leftJoin('obj.organizations', 'uo');
            $qb->leftJoin('uo.administrators', 'ua');
            $qb->andWhere('ua.id = :userId');
            $qb->setParameter('userId', $currentUser->getId());
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'organization':
                    $qb->leftJoin('obj.organizations', 'o');
                    $qb->andWhere('o.uuid IN (:organizationIds)');
                    $qb->setParameter('organizationIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'user':
                    $qb->leftJoin('obj.users', 'gu');
                    $qb->andWhere('gu.uuid IN (:userIds)');
                    $qb->setParameter('userIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'location':
                    $qb->leftJoin('obj.locations', 'l');
                    $qb->andWhere('l.uuid IN (:locationIds)');
                    $qb->setParameter('locationIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
              case 'role':
                  $qb->leftJoin('obj.roles', 'r');
                  $qb->andWhere('r.uuid IN (:roleIds)');
                  $qb->setParameter('roleIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                  break;
                default:
                    if (is_bool($filterValue)) {
                        $qb->andWhere("obj.{$filterName} = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    } else {
                        $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                        $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    }
            }
        }

        return $qb;
    }
}
