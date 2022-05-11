<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\Facet;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FieldFacetValueFinder extends AbstractFinder
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
        return 'Claroline\CoreBundle\Entity\Facet\FieldFacetValue';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        //use oauthChecker & tokenStorage to fetch exactly what's needed according to the permissions

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'user':
                    $qb->leftJoin('obj.user', 'user');
                    $qb->andWhere('user.uuid = :userId');
                    $qb->setParameter('userId', $filterValue);
                    break;
                case 'fieldFacet':
                    $qb->leftJoin('obj.fieldFacet', 'fieldFacet');
                    $qb->andWhere('fieldFacet.id = :fieldFacetId');
                    $qb->setParameter('fieldFacetId', $filterValue);
                    break;
            }
        }

        return $qb;
    }
}
