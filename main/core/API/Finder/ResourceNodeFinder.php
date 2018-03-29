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

use Claroline\AppBundle\API\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.api.finder.resource_node")
 * @DI\Tag("claroline.finder")
 */
class ResourceNodeFinder implements FinderInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    private $usedJoin = [];

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
        return 'Claroline\CoreBundle\Entity\Resource\ResourceNode';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        $qb->join('obj.resourceType', 'ort');
        $qb->join('obj.workspace', 'ow');

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'meta.type':
                case 'resourceType':
                    if (is_array($filterValue)) {
                        $qb->andWhere('ort.name IN (:resourceType)');
                    } else {
                        $qb->andWhere('ort.name LIKE :resourceType');
                    }
                    $qb->setParameter('resourceType', $filterValue);
                    break;
                case 'resourceTypeBlacklist':
                    if (is_array($filterValue)) {
                        $qb->andWhere("ort.name NOT IN (:{$filterName})");
                    }
                    $qb->setParameter($filterName, $filterValue);
                    break;
                case 'workspace.name':
                    $qb->andWhere('UPPER(ow.name) LIKE :workspace');
                    $qb->setParameter('workspace', '%'.strtoupper($filterValue).'%');
                    break;
                case 'meta.parent.name':
                    $qb->join('obj.parent', 'op');
                    $qb->andWhere('UPPER(op.name) LIKE :parent');
                    $qb->setParameter('parent', '%'.strtoupper($filterValue).'%');
                    $this->usedJoin['parent'] = true;
                    break;
                case 'roles':
                    $managerRoles = [];
                    $otherRoles = [];

                    foreach ($filterValue as $roleName) {
                        if (preg_match('/^ROLE_WS_MANAGER_/', $roleName)) {
                            $managerRoles[] = $roleName;
                        } else {
                            $otherRoles[] = $roleName;
                        }
                    }
                    $qb->leftJoin('ow.roles', 'owr');
                    $qb->leftJoin('obj.rights', 'rights');
                    $qb->join('rights.role', 'rightsr');
                    $qb->andWhere('(owr.name IN (:managerRoles)) OR ((rightsr.name IN (:otherRoles)) AND (BIT_AND(rights.mask, 1) = 1))');
                    $qb->setParameter('managerRoles', $managerRoles);
                    $qb->setParameter('otherRoles', $otherRoles);
                    break;
                default:
                    if (is_string($filterValue)) {
                        $qb->andWhere("UPPER(obj.{$filterName}) LIKE :{$filterName}");
                        $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                    } else {
                        $qb->andWhere("obj.{$filterName} = :{$filterName}");
                        $qb->setParameter($filterName, $filterValue);
                    }
                    break;
            }
        }

        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'meta.type':
                    $qb->orderBy('ort.name', $sortByDirection);
                    break;
                case 'workspace.name':
                    $qb->orderBy('ow.name', $sortByDirection);
                    break;
                case 'meta.parent.name':
                    if (!$this->usedJoin['parent']) {
                        $qb->join('obj.parent', 'op');
                    }
                    $qb->orderBy('op.name', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }
}
