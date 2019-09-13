<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FormaLibre\ReservationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ResourceFinder extends AbstractFinder
{
    private $authChecker;
    private $tokenStorage;

    /**
     * ResourceFinder constructor.
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
        return 'FormaLibre\ReservationBundle\Entity\Resource';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        if (!$this->authChecker->isGranted('ROLE_ADMIN')) {
            $currentUser = $this->tokenStorage->getToken()->getUser();
            $qb->join('obj.organizations', 'o');
            $qb->andWhere('o.id IN (:organizationsIds)');
            $qb->setParameter('organizationsIds', array_map(function (Organization $organization) {
                return $organization->getId();
            }, $currentUser->getOrganizations()));
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'resourceType.name':
                    $qb->join('obj.resourceType', 't');
                    $qb->andWhere('t.name = :resourceTypeName');
                    $qb->setParameter('resourceTypeName', $filterValue);
                    break;
                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
            }
        }

        return $qb;
    }
}
