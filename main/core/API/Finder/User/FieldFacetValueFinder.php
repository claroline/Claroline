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
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.api.finder.field_facet_value")
 * @DI\Tag("claroline.finder")
 */
class FieldFacetValueFinder implements FinderInterface
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
        return 'Claroline\CoreBundle\Entity\Facet\FieldFacetValue';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
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
