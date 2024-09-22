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
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Organization\UserOrganizationReference;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OrganizationFinder extends AbstractFinder
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getClass(): string
    {
        return Organization::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, ?int $page = 0, ?int $limit = -1): QueryBuilder
    {
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'parent':
                    if (empty($filterValue)) {
                        $qb->andWhere('obj.parent IS NULL');
                    } else {
                        $qb->leftJoin('obj.parent', 'p');
                        $qb->andWhere('p.uuid IN (:parentIds)');
                        $qb->setParameter('parentIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    }
                    break;
                case 'group':
                    $qb->leftJoin(Group::class, 'g', Join::WITH, 'obj MEMBER OF g.organizations');
                    $qb->andWhere('g.uuid IN (:groupIds)');
                    $qb->setParameter('groupIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'user':
                    $qb->leftJoin(UserOrganizationReference::class, 'ur', Join::WITH, 'ur.organization = obj');
                    $qb->leftJoin('ur.user', 'u');
                    $qb->andWhere('u.uuid IN (:userIds)');
                    $qb->setParameter('userIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'workspace':
                    $qb->leftJoin(Workspace::class, 'w', Join::WITH, 'obj MEMBER OF w.organizations');
                    $qb->andWhere('w.uuid IN (:workspaceIds)');
                    $qb->setParameter('workspaceIds', is_array($filterValue) ? $filterValue : [$filterValue]);
                    break;
                case 'administrated':
                    $qb->leftJoin(UserOrganizationReference::class, 'ur2', Join::WITH, 'ur2.organization = obj AND ur2.manager = 1');
                    $qb->leftJoin('obj.userOrganizationReferences', 'ur2');
                    $qb->leftJoin('ur2.user', 'u2');
                    $qb->andWhere('ur2.user = (:currentUserId)');
                    $qb->setParameter('currentUserId', $this->tokenStorage->getToken()?->getUser() ? $this->tokenStorage->getToken()?->getUser()->getId() : null);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
