<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder;

use Claroline\CoreBundle\API\FinderInterface;
use Claroline\CoreBundle\Entity\Workspace\Workspace as WorkspaceEntity;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.API.finder.workspace")
 * @DI\Tag("claroline.finder")
 */
class Workspace implements FinderInterface
{
    /**
     * @DI\InjectParams({
     *     "authChecker"      = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"     = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage)
    {
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [])
    {
        // retrieves searchable text fields
        $baseFieldsName = WorkspaceEntity::getWorkspaceSearchableFields();
        //Admin can see everything, but the others... well they can only see their own organizations.
        $customFields = ['createdAfter', 'createdBefore'];

        if (!$this->authChecker->isGranted('ROLE_ADMIN')) {
            /** @var User $currentUser */
            $currentUser = $this->tokenStorage->getToken()->getUser();
            $qb->leftJoin('obj.organizations', 'uo');
            $qb->leftJoin('uo.administrators', 'ua');
            $qb->andWhere('ua.id = :userId');
            $qb->setParameter('userId', $currentUser->getId());
        }

        foreach ($searches as $filterName => $filterValue) {
            // todo : add organization filter
            if (in_array($filterName, $baseFieldsName)) {
                $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
            } else {
                if (!in_array($filterName, $customFields)) {
                    if ('true' === $filterValue || 'false' === $filterValue) {
                        $filterValue = 'true' === $filterValue;
                        $qb->andWhere("obj.{$filterName} = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    }
                } else {
                    switch ($filterName) {
                      case 'createdAfter':
                          $qb->andWhere("obj.creationDate >= :{$filterName}");
                          $qb->setParameter($filterName, date('Y-m-d', $filterValue));
                          break;
                      case 'createdBefore':
                          $qb->andWhere("obj.creationDate <= :{$filterName}");
                          $qb->setParameter($filterName, date('Y-m-d', $filterValue));
                          break;
                  }
                }
            }
        }

        return $qb;
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Workspace\Workspace';
    }
}
