<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use Doctrine\ORM\AbstractQuery;

/**
 * Repository of methods to access AbstractResource entities.
 */
class AbstractResourceRepository extends MaterializedPathRepository
{
    /**
     * Returns the root resource of the workspace
     * @param AbstractWorkspace $ws
     * @return a resource resource entity
     */
    public function findWorkspaceRoot(AbstractWorkspace $ws)
    {
        $dql = '
            SELECT ar
            FROM Claroline\CoreBundle\Entity\Resource\AbstractResource ar
            WHERE ar.lvl = 1 AND ar.workspace = :ws_id
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('ws_id', $ws->getId());

        return $query->getOneOrNullResult();
    }

    /**
     * Returns the descendants of a resource. Always returns entities.
     *
     * @param AbstractResource  $resource           The resource node to start with
     * @param boolean           $includeStartNode   Whether the given resource should be included in the result
     * @param string            $filterResourceType A resource type to filter the results
     *
     * @return AbstractResource
     */
    public function findDescendants(
        AbstractResource $resource,
        $includeStartNode = false,
        $filterResourceType = null
    )
    {

        $builder = new ResourceQueryBuilder('SELECT ar');
        $builder->from()
            ->where()
            ->whereIsVisible(1)
            ->wherePath($resource->getPath(), $includeStartNode);

        if ($filterResourceType) {
            $builder->whereTypes(array($filterResourceType));
        }

        $dql = $builder->getDql();
        $query = $this->_em->createQuery($dql);
        $builder->setQueryParameters($query);

        return $this->executeQuery($query, null, null, false);
    }

    /**
     * Returns all direct resources under parent. Returns a list of entities or an array if requested.
     * Returns a list of rights for every resources.
     * The admin has every rights no matter what.
     *
     * @param AbstractResource $parent Parent ID of children that we request.
     * @param int $resourceTypeId ResourceType ID to filter on.
     * @param boolean $asArray returns a list of arrays if true, else a list of entities.
     * @param boolean $isVisible if true, returns only resources that are visible.
     * @param array roles an array of role (string)
     *
     * @return an array of arrays or entities
     */
    public function findChildren(AbstractResource $parent, $roles, $resourceType = null, $isVisible = true)
    {
        $isAdmin = false;

        foreach ($roles as $role) {
            if ($role === 'ROLE_ADMIN') {
                $isAdmin = true;
            }
        }

        if ($isAdmin) {
            $query = $this->buildAdminChildrenQuery($parent, $isVisible);
        } else {
            $query = $this->buildForRolesChildrenQuery($parent, $roles);
        }

        $results = array();

        if (!$isAdmin) {
            $results = $this->executeQuery($query);
        } else {
            $items = $query->iterate(null, AbstractQuery::HYDRATE_ARRAY);

            foreach ($items as $key => $item) {
                $item[$key]['can_export'] = true;
                $item[$key]['can_edit'] = true;
                $item[$key]['can_delete'] = true;
                $results[] = $item[$key];
            }
        }

        return $results;
    }

    /**
     * Returns the list of roots for the given user. Returns a list of entities or an array if requested.
     *
     * @param User $user Owner of the resources.
     * @param boolean $asArray returns a list of arrays if true, else a list of entities.
     *
     * @return an array of arrays or entities
     */
    public function findWorkspaceRootsByUser(User $user)
    {
        $builder = new ResourceQueryBuilder();
        $dql = $builder->select()
            ->from()
            ->where()
            ->whereParentIsNull()
            ->whereInUserWorkspace($user)
            ->orderByPath()
            ->getDql();

        $query = $this->_em->createQuery($dql);
        $builder->setQueryParameters($query);

        return $this->executeQuery($query);
    }

    /**
     * Returns an array of all ancestors of a abstractResource
     * (the resource itlsef is returned too).
     *
     * @param listDirectChildrenResourceInstances $resource The resource about which we want ancestors.
     *
     * @return array (name, id)
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
     * Returns all the resources a user can open, filtered with given criteria.
     *
     * @param Array $criterias Array of criterias to use to build the filter.
     * @param $roles as array of role (string)
     * @param boolean $asArray returns a list of arrays if true, else a list of entities.
     *
     * @return an array of arrays or entities
     */
    public function findUserResourcesByCriteria(User $user, array $criteria)
    {
        $builder = new ResourceQueryBuilder();
        $builder->select()
            ->from()
            ->joinRightsForUser($user)
            ->where()
            ->whereIsVisible(1)
            ->whereInUserWorkspace($user)
            ->whereCanOpen();

        foreach ($criteria as $key => $value) {
            if ($value != null) {
                $methodName = 'where' . ucfirst($key);
                $builder->$methodName($value);
            }
        }
        $dql = $builder->orderByPath()->getDql();
        $query = $this->_em->createQuery($dql);
        $builder->setQueryParameters($query);

        return $this->executeQuery($query);
    }

    /**
     * The children query for the admin (every resource is shown).
     *
     * @param AbstractResource $parent
     * @param ResourceType $resourceType
     * @param boolean $asArray
     * @param boolean $isVisible
     *
     * @return Query
     */
    private function buildAdminChildrenQuery(AbstractResource $parent, $isVisible)
    {
        $builder = new ResourceQueryBuilder();
        $builder->select()
            ->from()
            ->where()
            ->whereParent($parent)
            ->whereIsVisible($isVisible);

        $dql = $builder->getDql();
        $query = $this->_em->createQuery($dql);
        $builder->setQueryParameters($query);

        return $query;
    }

    /**
     * The children query an array of Roles.
     *
     * @param AbstractResource $parent
     * @param array roles
     * @param ResourceType $resourceType
     * @param boolean $asArray
     * @param boolean $isVisible
     *
     * @return Query
     */
    private function buildForRolesChildrenQuery(
        AbstractResource $parent,
        $roles,
        $isVisible = true
    )
    {

         $builder = new ResourceQueryBuilder();
         $builder->select()
             ->selectPermissions()
             ->from()
             ->leftJoinOnRightsAndRole()
             ->where();

        for ($i = 0, $count = count($roles); $i < $count; $i++) {
            if ($i != 0) {
                $builder->addClause('or');
            }

            $builder->whereParent($parent)
                ->whereIsVisible($isVisible)
                ->whereCanOpen()
                ->whereRole($roles[$i]);
        }

        $dql = $builder->groupById()->getDql();
        $query = $this->_em->createQuery($dql);
        $builder->setQueryParameters($query);

        return $query;
    }

    /**
     * Execute a DQL query and may return a list of entities or a list of arrays.
     * If it returns arrays, it add a "pathfordisplay" field in each item.
     *
     * @param Query $query The query to execute.
     * @param boolean $asArray Set it to true if you want the result as a list of arrays.
     * @param int $numrows Maximum number of rows to return.
     * @param int ResourceType $resourceType Resource type to filter on.
     *
     * @return array of arrays or array of entities
     */
    private function executeQuery($query, $offset = null, $numrows = null, $asArray = true)
    {
        $query->setFirstResult($offset);
        $query->setMaxResults($numrows);

        if ($asArray) {
            $res = $query->getArrayResult();
            // Add a field "pathfordisplay" in each entity (as array) of the given array.
            foreach ($res as &$r) {
                $r['pathfordisplay'] = AbstractResource::convertPathForDisplay($r["path"]);
                unset($r['path']);
            }

            return $res;
        }

        return $query->getResult();
    }
}