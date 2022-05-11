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
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GroupFinder extends AbstractFinder
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
        return 'Claroline\CoreBundle\Entity\Group';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $qb->andWhere('obj.isReadOnly = false');

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
              case 'workspace':
                  $qb->leftJoin('obj.roles', 'wsgroles');
                  $qb->leftJoin('wsgroles.workspace', 'rws');
                  $qb->andWhere('rws.uuid = (:workspaceId)');
                  $qb->setParameter('workspaceId', $filterValue);
                  break;
                default:
                  $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
