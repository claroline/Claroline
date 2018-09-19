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

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.api.finder.resource_node")
 * @DI\Tag("claroline.finder")
 */
class ResourceNodeFinder extends AbstractFinder
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    private $usedJoin = [];

    /**
     * ResourceNodeFinder constructor.
     *
     * @DI\InjectParams({
     *     "authChecker"  = @DI\Inject("security.authorization_checker"),
     *     "em"           = @DI\Inject("doctrine.orm.entity_manager"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param AuthorizationCheckerInterface $authChecker
     * @param TokenStorageInterface         $tokenStorage
     * @param EntityManager                 $em
     */
    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        TokenStorageInterface $tokenStorage,
        EntityManager $em
    ) {
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
        $this->_em = $em;
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Resource\ResourceNode';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $qb->join('obj.resourceType', 'ort');
        $qb->join('obj.workspace', 'ow');

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'meta.published':
                    $qb->andWhere('obj.published LIKE :published');
                    $qb->setParameter('published', $filterValue);
                    break;
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
                case 'resourceTypeEnabled':
                    $qb->andWhere("ort.isEnabled = :{$filterName}");
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
                case 'parent':
                    if (is_null($filterValue)) {
                        $qb->andWhere('obj.parent IS NULL');
                    } else {
                        $qb->andWhere('obj.parent = :parent');
                        $qb->setParameter('parent', $filterValue);
                    }
                    break;
                case 'managerRole':
                    $managerRoles = [];
                    foreach ($filterValue as $roleName) {
                        if (preg_match('/^ROLE_WS_MANAGER_/', $roleName)) {
                            $managerRoles[] = $roleName;
                        }
                    }

                    $qb->leftJoin('ow.roles', 'owr');
                    $qb->leftJoin('obj.rights', 'rights');
                    $qb->join('rights.role', 'rightsr');
                    $qb->andWhere('owr.name IN (:managerRoles)');
                    $qb->setParameter('managerRoles', $managerRoles);
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

                    $managerSearch = $roleSearch = $searches;
                    $managerSearch['_managerRoles'] = $managerRoles;
                    $roleSearch['_roles'] = $otherRoles;
                    unset($managerSearch['roles']);
                    unset($roleSearch['roles']);
                    unset($searches['roles']);

                    $qbManager = $this->om->createQueryBuilder();
                    $qbManager->select('DISTINCT obj')->from($this->getClass(), 'obj');
                    $this->configureQueryBuilder($qbManager, $managerSearch, $sortBy);
                    //this is our first part of the union
                    $sqlManager = $this->getSql($qbManager->getQuery());
                    $sqlManager = $this->removeAlias($sqlManager);

                    $qbRoles = $this->om->createQueryBuilder();
                    $qbRoles->select('DISTINCT obj')->from($this->getClass(), 'obj');
                    $this->configureQueryBuilder($qbRoles, $roleSearch, $sortBy);
                    //this is the second part of the union
                    $sqlRoles = $this->getSql($qbRoles->getQuery());
                    $sqlRoles = $this->removeAlias($sqlRoles);
                    $together = $sqlManager.' UNION '.$sqlRoles;

                    //we might want to add a count somehere here
                    //add limit & offset too

                    if ($options['count']) {
                        $together = "SELECT COUNT(*) as count FROM ($together) AS wathever";
                        $rsm = new ResultSetMapping();
                        $rsm->addScalarResult('count', 'count', 'integer');
                        $query = $this->_em->createNativeQuery($together, $rsm);
                    } else {
                        //add page & limit
                        if ($options['limit'] > -1) {
                            $together .= ' LIMIT '.$options['limit'];
                        }

                        if ($options['limit'] > 0) {
                            $offset = $options['limit'] * $options['page'];
                            $together .= ' OFFSET  '.$offset;
                        }

                        $rsm = new ResultSetMappingBuilder($this->_em);
                        $rsm->addRootEntityFromClassMetadata($this->getClass(), 'c0_');
                        $query = $this->_em->createNativeQuery($together, $rsm);
                    }

                    return $query;

                    break;
                case '_managerRoles':
                    $qb->leftJoin('ow.roles', 'owr');
                    $qb->andWhere('owr.name IN (:managerRoles)');

                    $qb->setParameter('managerRoles', $filterValue);
                    break;
                case '_roles':
                    $qb->leftJoin('obj.rights', 'rights');
                    $qb->join('rights.role', 'rightsr');
                    $qb->andWhere('rightsr.name IN (:otherRoles)');
                    $qb->andWhere('BIT_AND(rights.mask, 1) = 1');
                    $qb->setParameter('otherRoles', $filterValue);
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
                case 'meta.updated':
                    $qb->orderBy('obj.modificationDate', $sortByDirection);
                    break;
                case 'meta.created':
                    $qb->orderBy('obj.creationDate', $sortByDirection);
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

    public function removeAlias($sql)
    {
        $aliases = [
          'AS license_0',
          'AS creation_date_1',
          'AS modification_date_2',
          'AS showIcon_3',
          'AS name_4',
          'AS hidden_5',
          'AS lvl_6',
          'AS path_7',
          'AS value_8',
          'AS mime_type_9',
          'AS published_10',
          'AS published_to_portal_11',
          'AS author_12',
          'AS active_13',
          'AS fullscreen_14',
          'AS closable_15',
          'AS closeTarget_16',
          'AS accesses_17',
          'AS views_count_18',
          'AS deletable_19',
          'AS id_20',
          'AS uuid_21',
          'AS thumbnail_22',
          'AS poster_23 ',
          'AS description_24',
          'AS accessible_from_25',
          'AS accessible_until_26',
          'AS resource_type_id_27',
          'AS icon_id_28',
          'AS parent_id_29',
          'AS workspace_id_30',
          'AS creator_id_31',
        ];

        foreach ($aliases as $alias) {
            $sql = str_replace($alias, '', $sql);
        }

        return $sql;
    }
}
