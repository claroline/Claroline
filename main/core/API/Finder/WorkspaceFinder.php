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
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.api.finder.workspace")
 * @DI\Tag("claroline.finder")
 */
class WorkspaceFinder implements FinderInterface
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
        return 'Claroline\CoreBundle\Entity\Workspace\Workspace';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [])
    {
        if (php_sapi_name() !== 'cli' && !$this->authChecker->isGranted('ROLE_ADMIN')) {
            /** @var User $currentUser */
            $currentUser = $this->tokenStorage->getToken()->getUser();
            $qb->leftJoin('obj.organizations', 'uo');
            $qb->leftJoin('uo.administrators', 'ua');
            $qb->andWhere('ua.id = :userId');
            $qb->setParameter('userId', $currentUser->getId());
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
              case 'createdAfter':
                  $qb->andWhere("obj.creationDate >= :{$filterName}");
                  $qb->setParameter($filterName, $filterValue);
                  break;
              case 'createdBefore':
                  $qb->andWhere("obj.creationDate <= :{$filterName}");
                  $qb->setParameter($filterName, $filterValue);
                  break;
              default:
                if ('true' === $filterValue || 'false' === $filterValue || true === $filterValue || false === $filterValue) {
                    $filterValue = is_string($filterValue) ? 'true' === $filterValue : $filterValue;
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
