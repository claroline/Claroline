<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Repository\Exception\UnknownFilterException;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Repository for AbstractResource entities. The methods of this class may return
 * entities either as objects or as as arrays (see their respective documentation).
 */
class ResourceNodeRepository extends MaterializedPathRepository implements ContainerAwareInterface
{
    private $container;
    private $builder;
    private $bundles = [];

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $bundles = $this->container->get('claroline.manager.plugin_manager')->getEnabled(true);
        $this->builder = new ResourceQueryBuilder();
        $this->builder->setBundles($bundles);
    }

    /**
     * Finds a resource node by its id or guid.
     *
     * @param string|int $id The id or guid of the node
     *
     * @return ResourceNode|null
     */
    public function find($id)
    {
        $qb = $this->createQueryBuilder('n');

        if (preg_match('/^\d+$/', $id)) {
            $qb->where('n.id = :id');
        } else {
            $qb->where('n.guid = :id');
        }

        return $qb
            ->getQuery()
            ->setParameter('id', $id)
            ->getOneOrNullResult();
    }

    /**
     * Returns the root directory of a workspace.
     *
     * @param Workspace $workspace
     *
     * @return ResourceNode
     */
    public function findWorkspaceRoot(Workspace $workspace)
    {
        $this->builder->selectAsEntity()
            ->whereInWorkspace($workspace)
            ->whereParentIsNull();
        $query = $this->_em->createQuery($this->builder->getDql());
        $query->setParameters($this->builder->getParameters());

        return $query->getOneOrNullResult();
    }

    /**
     * Returns the descendants of a resource.
     *
     * @param ResourceNode $resource           The resource node to start with
     * @param bool         $includeStartNode   Whether the given resource should be included in the result
     * @param string       $filterResourceType A resource type to filter the results
     *
     * @return array[ResourceNode]
     */
    public function findDescendants(
        ResourceNode $resource,
        $includeStartNode = false,
        $filterResourceType = null
    ) {
        $this->builder->selectAsEntity(true)
            ->wherePathLike($resource->getPath(), $includeStartNode);

        if ($filterResourceType) {
            $this->builder->whereTypeIn([$filterResourceType]);
        }

        $query = $this->_em->createQuery($this->builder->getDql());
        $query->setParameters($this->builder->getParameters());

        return $this->executeQuery($query, null, null, false);
    }

    /**
     * Returns the immediate children of a resource that are openable by any of the given roles.
     *
     * @param ResourceNode $parent The id of the parent of the requested children
     * @param array        $roles  [string] $roles  An array of roles
     * @param User         $user   the user opening
     * @param withLastOpenDate with the last openend node (with the last opened date)
     *
     * @throws \RuntimeException
     * @throw InvalidArgumentException if the array of roles is empty
     *
     * @return array[array] An array of resources represented as arrays
     */
    public function findChildren(ResourceNode $parent, array $roles, $user, $withLastOpenDate = false, $canAdministrate = false)
    {
        //if we usurpate a role, then it's like we're anonymous.
        if (in_array('ROLE_USURPATE_WORKSPACE_ROLE', $roles)) {
            $user = 'anon.';
        }

        if (count($roles) === 0) {
            throw new \RuntimeException('Roles cannot be empty');
        }

        $returnedArray = [];

        $isWorkspaceManager = $this->isWorkspaceManager($parent, $roles);
        //check if manager of the workspace.
        //if it's true, show every children
        if ($isWorkspaceManager) {
            $this->builder->selectAsArray()
                ->whereParentIs($parent)
                ->whereActiveIs(true)
                ->orderByIndex();
            $query = $this->_em->createQuery($this->builder->getDql());
            $query->setParameters($this->builder->getParameters());
            $items = $query->iterate(null, AbstractQuery::HYDRATE_ARRAY);

            foreach ($items as $key => $item) {
                $item[$key]['mask'] = 65535;
                $returnedArray[] = $item[$key];
            }
        //otherwise only show visible children
        } else {
            $this->builder->selectAsArray(true)
                ->whereParentIs($parent)
                ->whereActiveIs(true)
                ->whereHasRoleIn($roles);
            if (!$canAdministrate) {
                $this->builder->whereIsAccessible($user);
            }

            $query = $this->_em->createQuery($this->builder->getDql());
            $query->setParameters($this->builder->getParameters());

            $children = $this->executeQuery($query);
            $childrenWithMaxRights = [];

            foreach ($children as $child) {
                if (!isset($childrenWithMaxRights[$child['id']])) {
                    $childrenWithMaxRights[$child['id']] = $child;
                }

                foreach ($childrenWithMaxRights as $id => $childMaxRights) {
                    if ($id === $child['id']) {
                        $childrenWithMaxRights[$id]['mask'] |= $child['mask'];
                    }
                }
            }

            $returnedArray = [];

            foreach ($childrenWithMaxRights as $childMaxRights) {
                $returnedArray[] = $childMaxRights;
            }
        }

        //now we get the last open date for nodes.
        //We can't do one request because of the left join + max combination

        if ($withLastOpenDate && $user !== 'anon.') {
            $this->builder->selectAsArray(false, true)
                ->whereParentIs($parent)
                ->addLastOpenDate($user)
                ->groupById();

            if (!$isWorkspaceManager) {
                $this->builder->whereHasRoleIn($roles)->whereIsAccessible($user);
            }

            $query = $this->_em->createQuery($this->builder->getDql());
            $query->setParameters($this->builder->getParameters());
            $items = $this->executeQuery($query);

            foreach ($returnedArray as $key => $returnedElement) {
                foreach ($items as $item) {
                    if ($item['id'] === $returnedElement['id']) {
                        $returnedArray[$key]['last_opened'] = $item['last_opened'];
                    }
                }
            }
        }

        //and now we order by index
        usort($returnedArray, function ($a, $b) {
            if ($a['index_dir'] === $b['index_dir']) {
                return 0;
            }

            return ($a['index_dir'] < $b['index_dir']) ? -1 : 1;
        });

        return $returnedArray;
    }

    /**
     * Returns the root directories of workspaces a user is registered to.
     *
     * @param User $user
     *
     * @return array[array] An array of resources represented as arrays
     */
    public function findWorkspaceRootsByUser(User $user)
    {
        $dql = $this->builder->selectAsArray()
            ->whereParentIsNull()
            ->whereInUserWorkspace($user)
            ->orderByPath()
            ->getDql();
        $query = $this->_em->createQuery($dql);
        $query->setParameters($this->builder->getParameters());

        return $this->executeQuery($query);
    }

    /**
     * Returns the roots directories a user is granted access.
     *
     * @param array $roles
     *
     * @return array[array] An array of resources represented as arrays
     */
    public function findWorkspaceRootsByRoles(array $roles)
    {
        $dql = $this->builder->selectAsArray()
            ->whereParentIsNull()
            ->whereHasRoleIn($roles)
            ->orderByName()
            ->getDql();

        $query = $this->_em->createQuery($dql);
        $query->setParameters($this->builder->getParameters());

        return $this->executeQuery($query);
    }

    /**
     * Returns the ancestors of a resource, including the resource itself.
     *
     * @param ResourceNode $resource
     *
     * @return array[array] An array of resources represented as arrays
     */
    public function findAncestors(ResourceNode $resource)
    {
        // No need to access DB to get ancestors as they are given by the materialized path.
        $regex = '/-(\d+)'.ResourceNode::PATH_SEPARATOR.'/';
        $parts = preg_split($regex, $resource->getPath(), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $ancestors = [];
        $currentPath = '';

        for ($i = 0, $count = count($parts); $i < $count; $i += 2) {
            $ancestor = [];
            $currentPath = $currentPath.$parts[$i].'-'.$parts[$i + 1].'`';
            $ancestor['path'] = $currentPath;
            $ancestor['name'] = $parts[$i];
            $ancestor['id'] = (int) $parts[$i + 1];
            $ancestors[] = $ancestor;
        }

        return $ancestors;
    }

    /**
     * Returns the resources matching a set of given criterias. If an array
     * of roles is passed, only the resources that can be opended by any of
     * these roles are matched.
     * WARNING: the recursive search is far from being optimized.
     *
     * @param array $criteria An array of search filters
     * @param array $roles    An array of user's roles
     *
     * @return array[array] An array of resources represented as arrays
     */
    public function findByCriteria(array $criteria, array $roles = null)
    {
        $this->builder->selectAsArray();
        $this->addFilters($this->builder, $criteria, $roles);
        $dql = $this->builder->orderByPath()->getDql();
        $query = $this->_em->createQuery($dql);
        $query->setParameters($this->builder->getParameters());
        $resources = $query->getResult();

        return $resources;
    }

    /**
     * Returns an array of different file types with the number of resources that
     * belong to this type.
     *
     * @param int $max
     *
     * @return array
     */
    public function findMimeTypesWithMostResources($max)
    {
        $qb = $this->createQueryBuilder('resource');
        $qb->select('resource.mimeType AS type, COUNT(resource.id) AS total')
            ->where($qb->expr()->isNotNull('resource.mimeType'))
            ->groupBy('resource.mimeType')
            ->orderBy('total', 'DESC');

        if ($max > 1) {
            $qb->setMaxResults($max);
        }

        return $qb->getQuery()->getResult();
    }

    public function findLastIndex(ResourceNode $node)
    {
        $dql = '
            SELECT MAX(node.index)
            FROM Claroline\CoreBundle\Entity\Resource\ResourceNode node
            where node.parent = :node';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('node', $node->getId());

        return $query->getSingleScalarResult();
    }

    /**
     * Returns the workspace name and code of the resources whose ids are passed
     * as argument.
     *
     * @param array $resourceIds
     *
     * @return array
     *
     * @throws \InvalidArgumentException if the resource ids array is empty
     */
    public function findWorkspaceInfoByIds(array $nodesIds)
    {
        if (count($nodesIds) === 0) {
            throw new \InvalidArgumentException('Resource ids array cannot be empty');
        }

        $dql = '
            SELECT r.id AS id, w.code AS code, w.name AS name
            FROM Claroline\CoreBundle\Entity\Resource\ResourceNode r
            JOIN r.workspace w
            WHERE r.id IN (:nodeIds)
            ORDER BY w.name ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('nodeIds', $nodesIds);

        return $query->getResult();
    }

    public function findByMimeTypeAndParent($mimeType, ResourceNode $parent, array $roles)
    {
        if (!$this->isWorkspaceManager($parent, $roles)) {
            $dql = $this->builder->selectAsEntity(false, 'Claroline\CoreBundle\Entity\Resource\File')
                ->whereParentIs($parent)
                ->whereMimeTypeIs('%'.$mimeType.'%')
                ->whereHasRoleIn($roles)
                ->getDql();
        } else {
            $dql = $this->builder->selectAsEntity(false, 'Claroline\CoreBundle\Entity\Resource\File')
                ->whereParentIs($parent)
                ->whereMimeTypeIs('%'.$mimeType.'%')
                ->getDql();
        }

        $query = $this->_em->createQuery($dql);
        $query->setParameters($this->builder->getParameters());
        $resources = $query->getResult();

        return $resources;
    }

    public function findByWorkspaceAndResourceType(Workspace $workspace, ResourceType $resourceType)
    {
        $qb = $this->createQueryBuilder('resourceNode');
        $qb->select('resourceNode')
            ->where('resourceNode.workspace = :workspace')
            ->andWhere('resourceNode.resourceType = :resourceType');

        return $qb->getQuery()->execute(
            [
                ':workspace' => $workspace,
                ':resourceType' => $resourceType,
            ]
        );
    }

    /**
     * @param string $name
     * @param array  $extraDatas
     * @param bool   $executeQuery
     *
     * @return QueryBuilder|array
     */
    public function findByName($name, $extraDatas = [], $executeQuery = true)
    {
        $name = strtoupper($name);
        /** @var \Doctrine\ORM\QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder('resourceNode');
        $queryBuilder->where($queryBuilder->expr()->like('UPPER(resourceNode.name)', ':name'));

        if (0 < count($extraDatas)) {
            foreach ($extraDatas as $key => $extraData) {
                $queryBuilder
                    ->andWhere(sprintf('resourceNode.%s = :%s', $key, $key))
                    ->setParameter(sprintf(':%s', $key), $extraData);
            }
        }

        $queryBuilder
            ->orderBy('resourceNode.name', 'ASC')
            ->setParameter(':name', "%{$name}%");

        return $executeQuery ? $queryBuilder->getQuery()->getResult() : $queryBuilder;
    }

    /**
     * @param string $search
     * @param array  $extraData
     *
     * @return array
     */
    public function findByNameForAjax($search, $extraData)
    {
        $resultArray = [];

        /** @var ResourceNode[] $resourceNodes */
        $resourceNodes = $this->findByName($search, $extraData);

        foreach ($resourceNodes as $resourceNode) {
            $resultArray[] = [
                'id' => $resourceNode->getId(),
                'text' => $resourceNode->getPathForDisplay(),
            ];
        }

        return $resultArray;
    }

    private function addFilters(ResourceQueryBuilder $builder,  array $criteria, array $roles = null)
    {
        if ($roles) {
            if (!in_array('ROLE_ADMIN', $roles)) {
                //this should handle the workspace manager filter
                $builder->whereHasRoleIn($roles);
            }
        }

        $filterMethodMap = [
            'types' => 'whereTypeIn',
            'roots' => 'whereRootIn',
            'dateFrom' => 'whereDateFrom',
            'dateTo' => 'whereDateTo',
            'name' => 'whereNameLike',
            'isExportable' => 'whereIsExportable',
            'active' => 'whereActiveIs',
        ];
        $allowedFilters = array_keys($filterMethodMap);

        foreach ($criteria as $filter => $value) {
            if ($value !== null) {
                if (in_array($filter, $allowedFilters)) {
                    $builder->{$filterMethodMap[$filter]}($value);
                } else {
                    throw new UnknownFilterException("Unknown filter '{$filter}'");
                }
            }
        }

        return $builder;
    }

    /**
     * Executes a DQL query and returns resources as entities or arrays.
     * If it returns arrays, it add a "pathfordisplay" field to each item.
     *
     * @param Query $query   The query to execute
     * @param int   $offset  First row to start with
     * @param int   $numrows Maximum number of rows to return
     * @param bool  $asArray Whether the resources must be returned as arrays or as objects
     *
     * @return array[AbstractResource|array]
     */
    private function executeQuery($query, $offset = null, $numrows = null, $asArray = true)
    {
        $query->setFirstResult($offset);
        $query->setMaxResults($numrows);

        if ($asArray) {
            $resources = $query->getArrayResult();
            $return = $resources;
            // Add a field "pathfordisplay" in each entity (as array) of the given array.
            foreach ($resources as $key => $resource) {
                if (isset($resource['path'])) {
                    $return[$key]['path_for_display'] = ResourceNode::convertPathForDisplay($resource['path']);
                }
            }

            return $return;
        }

        return $query->getResult();
    }

    private function isWorkspaceManager(ResourceNode $node, array $roles)
    {
        $rolenames = [];

        foreach ($roles as $role) {
            if ($role instanceof Role) {
                $rolenames[] = $role->getRole();
            } else {
                $rolenames[] = $role;
            }
        }

        $isWorkspaceManager = false;
        $ws = $node->getWorkspace();
        $managerRole = 'ROLE_WS_MANAGER_'.$ws->getGuid();

        if (in_array($managerRole, $rolenames)) {
            $isWorkspaceManager = true;
        }

        if (in_array('ROLE_ADMIN', $rolenames)) {
            $isWorkspaceManager = true;
        }

        return $isWorkspaceManager;
    }

    public function findByWorkspaceAndMimeType(Workspace $workspace, $mimeType)
    {
        $dql = '
            SELECT r FROM Claroline\CoreBundle\Entity\Resource\ResourceNode r
            WHERE r.workspace = :workspace
            and r.mimeType LIKE :mimeType
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('mimeType', "%{$mimeType}%");

        return $query->getResult();
    }

    public function findResourcesByIds(array $roles, $user, array $ids)
    {
        //if we usurpate a role, then it's like we're anonymous.
        if (in_array('ROLE_USURPATE_WORKSPACE_ROLE', $roles)) {
            $user = 'anon.';
        }
        if (count($roles) === 0) {
            throw new \RuntimeException('Roles cannot be empty');
        }
        if (count($ids) === 0) {
            throw new \RuntimeException('List of id cannot be empty');
        }
        $this->builder->selectAsArray(true)
            ->whereIdIn($ids)
            ->whereActiveIs(true)
            ->whereHasRoleIn($roles)
            ->whereIsAccessible($user);

        $query = $this->_em->createQuery($this->builder->getDql());
        $query->setParameters($this->builder->getParameters());

        $children = $this->executeQuery($query);
        $childrenWithMaxRights = [];

        foreach ($children as $child) {
            if (!isset($childrenWithMaxRights[$child['id']])) {
                $childrenWithMaxRights[$child['id']] = $child;
            }

            foreach ($childrenWithMaxRights as $id => $childMaxRights) {
                if ($id === $child['id']) {
                    $childrenWithMaxRights[$id]['mask'] |= $child['mask'];
                }
            }
        }
        $returnedArray = [];

        foreach ($childrenWithMaxRights as $childMaxRights) {
            $returnedArray[] = $childMaxRights;
        }

        //and now we order by index
        usort($returnedArray, function ($a, $b) {
            if ($a['index_dir'] === $b['index_dir']) {
                return 0;
            }

            return ($a['index_dir'] < $b['index_dir']) ? -1 : 1;
        });

        return $returnedArray;
    }
}
