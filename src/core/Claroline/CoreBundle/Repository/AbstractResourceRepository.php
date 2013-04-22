<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Repository\Exception\UnknownFilterException;

/**
 * Repository for AbstractResource entities. The methods of this class may return
 * entities either as objects or as as arrays (see their respective documentation).
 */
class AbstractResourceRepository extends MaterializedPathRepository
{
    /**
     * Returns the root directory of a workspace.
     *
     * @param AbstractWorkspace $workspace
     *
     * @return AbstractResource
     */
    public function findWorkspaceRoot(AbstractWorkspace $workspace)
    {
        $builder = new ResourceQueryBuilder();
        $builder->selectAsEntity()
            ->whereInWorkspace($workspace)
            ->whereParentIsNull();
        $query = $this->_em->createQuery($builder->getDql());
        $query->setParameters($builder->getParameters());

        return $query->getOneOrNullResult();
    }

    /**
     * Returns the descendants of a resource.
     *
     * @param AbstractResource  $resource           The resource node to start with
     * @param boolean           $includeStartNode   Whether the given resource should be included in the result
     * @param string            $filterResourceType A resource type to filter the results
     *
     * @return array[AbstractResource]
     */
    public function findDescendants(
        AbstractResource $resource,
        $includeStartNode = false,
        $filterResourceType = null
    )
    {
        $builder = new ResourceQueryBuilder();
        $builder->selectAsEntity(true)
            ->wherePathLike($resource->getPath(), $includeStartNode);

        if ($filterResourceType) {
            $builder->whereTypeIn(array($filterResourceType));
        }

        $query = $this->_em->createQuery($builder->getDql());
        $query->setParameters($builder->getParameters());

        return $this->executeQuery($query, null, null, false);
    }

    /**
     * Returns the immediate children of a resource that are openable by any of the given roles.
     *
     * @param AbstractResource  $parent The id of the parent of the requested children
     * @param array[string]     $roles  An array of roles
     *
     * @throw InvalidArgumentException if the array of roles is empty
     *
     * @return array[array] An array of resources represented as arrays
     */
    public function findChildren(AbstractResource $parent, array $roles)
    {
        if (count($roles) === 0) {
            throw new \RuntimeException('Roles cannot be empty');
        }

        $builder = new ResourceQueryBuilder();
        $children = array();

        if (in_array('ROLE_ADMIN', $roles)) {
            $builder->selectAsArray()
                ->whereParentIs($parent)
                ->orderByName();
            $query = $this->_em->createQuery($builder->getDql());
            $query->setParameters($builder->getParameters());
            $items = $query->iterate(null, AbstractQuery::HYDRATE_ARRAY);

            foreach ($items as $key => $item) {
                $item[$key]['can_export'] = true;
                $item[$key]['can_edit'] = true;
                $item[$key]['can_delete'] = true;
                $children[] = $item[$key];
            }
        } else {
            $builder->selectAsArray(true)
                ->whereParentIs($parent)
                ->whereRoleIn($roles)
                ->whereCanOpen()
                ->groupByResourceUserTypeAndIcon();
            $query = $this->_em->createQuery($builder->getDql());
            $query->setParameters($builder->getParameters());
            $children = $this->executeQuery($query);
        }

        return $children;
    }

    /**
     * Returns the root directories a user has access to.
     *
     * @param User $user
     *
     * @return array[array] An array of resources represented as arrays
     */
    public function findWorkspaceRootsByUser(User $user)
    {
        $builder = new ResourceQueryBuilder();
        $dql = $builder->selectAsArray()
            ->whereParentIsNull()
            ->whereInUserWorkspace($user)
            ->orderByPath()
            ->getDql();
        $query = $this->_em->createQuery($dql);
        $query->setParameters($builder->getParameters());

        return $this->executeQuery($query);
    }

    /**
     * Returns the ancestors of a resource, including the resource itself.
     *
     * @param AbstractResource $resource
     *
     * @return array[array] An array of resources represented as arrays
     */
    public function findAncestors(AbstractResource $resource)
    {
        // No need to access DB to get ancestors as they are given by the materialized path.
        $regex = '/-(\d+)' . AbstractResource::PATH_SEPARATOR . '/';
        $parts = preg_split($regex, $resource->getPath(), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $ancestors = array();
        $currentPath = '';

        for ($i = 0, $count = count($parts); $i < $count; $i += 2) {
            $ancestor = array();
            $currentPath = $currentPath . $parts[$i] . '-' . $parts[$i + 1] . '`';
            $ancestor['path'] = $currentPath;
            $ancestor['name'] = $parts[$i];
            $ancestor['id'] = $parts[$i + 1];
            $ancestors[] = $ancestor;
        }

        return $ancestors;
    }

    /**
     * Returns the resources matching a set of given criterias. If an array
     * of roles is passed, only the resources that can be opended by any of
     * these roles are matched.
     *
     * @param array $criteria   An array of search filters
     * @param array $roles      An array of user's roles
     *
     * @return array[array] An array of resources represented as arrays
     */
    public function findByCriteria(array $criteria, array $roles = null)
    {
        $builder = new ResourceQueryBuilder();
        $builder->selectAsArray();

        if ($roles) {
            $builder->whereRoleIn($roles)
                ->whereCanOpen();
        }

        $filterMethodMap = array(
            'types' => 'whereTypeIn',
            'roots' => 'whereRootIn',
            'dateFrom' => 'whereDateFrom',
            'dateTo' => 'whereDateTo',
            'name' => 'whereNameLike',
            'isExportable' => 'whereIsExportable'
        );
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

        $dql = $builder->orderByPath()->getDql();
        $query = $this->_em->createQuery($dql);
        $query->setParameters($builder->getParameters());

        return $this->executeQuery($query);
    }

    /**
     * Executes a DQL query and returns resources as entities or arrays.
     * If it returns arrays, it add a "pathfordisplay" field in each item.
     *
     * @param Query   $query    The query to execute
     * @param integer $offset   First row to start with
     * @param integer $numrows  Maximum number of rows to return
     * @param boolean $asArray  Whether the resources must be returned as arrays or as objects
     *
     * @return array[AbstractResource|array]
     */
    private function executeQuery($query, $offset = null, $numrows = null, $asArray = true)
    {
        $query->setFirstResult($offset);
        $query->setMaxResults($numrows);

        if ($asArray) {
            $resources = $query->getArrayResult();
            // Add a field "pathfordisplay" in each entity (as array) of the given array.
            foreach ($resources as $resource) {

                if (isset($resource['path'])) {
                    $resource['pathfordisplay'] = AbstractResource::convertPathForDisplay($resource['path']);
                    unset($resource['path']);
                }
            }

            return $resources;
        }

        return $query->getResult();
    }

    public function count()
    {
        $dql = "SELECT COUNT(w) FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleScalarResult();
    }
}