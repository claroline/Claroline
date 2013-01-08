<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use Doctrine\ORM\AbstractQuery;

/**
 * Repository of methods to access AbstractResource entities.
 */
class AbstractResourceRepository extends MaterializedPathRepository
{
    /** SELECT DQL part to get entities. */
    //  Technical note:
    //      Selecting "ar" is needed to force Doctrine to load both entities
    //      at the same time (same SQL request) else doctrine will make N requests
    //      to get "ar" information.
    //      That's also the reason why we do not use
    //      the "MaterializedPathRepository->getChildren" method.

    const SELECT_FOR_ENTITIES = "ar";

    /** SELECT DQL part to get array. Please add any required field here. */
    const SELECT_FOR_ARRAY = "
            ar.id as id,
            ar.name as name,
            ar.path as path,
            IDENTITY(ar.parent) as parent_id,
            aru.username as creator_username,
            rt.name as type,
            rt.isBrowsable as is_browsable,
            ic.relativeUrl as large_icon ";

    /** FROM DQL part to join all needed entities. */
    const FROM_RESOURCES = "
            Claroline\CoreBundle\Entity\Resource\AbstractResource ar
            JOIN ar.creator aru
            JOIN ar.resourceType rt
            JOIN ar.icon ic ";

    /** FROM DQL part to join all needed entities. Warning: need to bind :u_id to userid. */
    const WHERECONDITION_USER_WORKSPACE = "
            ar.workspace IN
            ( SELECT aw FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace aw
                JOIN aw.roles r JOIN r.users u WHERE u.id = :u_id ) ";

    /**
     * Returns the root resource of the workspace
     * @param AbstractWorkspace $ws
     * @return a resource resource entity
     */
    public function getRootForWorkspace(AbstractWorkspace $ws)
    {
        $dql = "SELECT ar FROM Claroline\CoreBundle\Entity\Resource\AbstractResource ar
                WHERE ar.lvl = 1 AND ar.workspace = :ws_id";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('ws_id', $ws->getId());

        return $query->getOneOrNullResult();
    }

    /**
     * Returns all resources under parent. Returns a list of entities or an array if requested.
     *
     * @param AbstractResource $parent Parent of children that we request.
     * @param int ResourceType $resourceType Resource type to filter on.
     * @param boolean $asArray returns a list of arrays if true, else a list of entities.
     *
     * @return an array of arrays or entities
     */
    public function getDescendant(AbstractResource $parent, ResourceType $resourceType = null, $asArray = false)
    {
        $dql = "SELECT " . ($asArray ? self::SELECT_FOR_ARRAY : self::SELECT_FOR_ENTITIES)
                . " FROM " . self::FROM_RESOURCES
                . "WHERE (ar.path LIKE :pathlike AND ar.path <> :path)";

        if ($resourceType !== null){
            $dql.= "AND rt.name = :rt_name";
        }

        $query = $this->_em->createQuery($dql);

        if ($resourceType !== null){
            $query->setParameter('rt_name', $resourceType->getName());
        }

        $query->setParameter('pathlike', $parent->getPath() . '%');
        $query->setParameter('path', $parent->getPath());

        return $this->executeQuery($query, $asArray);
    }

    /**
     * Returns all direct resources under parent. Returns a list of entities or an array if requested.
     * Returns a list of rights for every resources.
     * The admin has every rights no matter what.
     *
     * @param int $parentId Parent ID of children that we request.
     * @param int $resourceTypeId ResourceType ID to filter on.
     * @param boolean $asArray returns a list of arrays if true, else a list of entities.
     * @param boolean $isVisible if true, returns only resources that are visible.
     * @param User $user the current user
     *
     * @return an array of arrays or entities
     */
    public function children($parentId, User $user, $resourceTypeId = 0, $asArray = false, $isVisible = true)
    {
        $dql = "SELECT DISTINCT ";
        $isAdmin = false;

       foreach($user->getRoles() as $role){
           if($role == 'ROLE_ADMIN'){
               $isAdmin = true;
           }
        }

        if($asArray){
            $dql.=  self::SELECT_FOR_ARRAY;

            if(!$isAdmin){
                $dql.= ' , arRights.canExport as can_export, arRights.canDelete as can_delete, arRights.canEdit as can_edit';
            }

        } else {
            if(!$isAdmin){
                $dql.= self::SELECT_FOR_ENTITIES. ', arRights';
            } else {
                //maybe sets every rights should be set to true
                $dql.= self::SELECT_FOR_ENTITIES;
            }
        }

       $dql.= " FROM " . self::FROM_RESOURCES;


        if ($isAdmin == false){
            $dql .= "
                LEFT JOIN ar.rights arRights
                JOIN arRights.role rightRole WITH rightRole IN (
                    SELECT currentUserRole FROM Claroline\CoreBundle\Entity\Role currentUserRole
                    JOIN currentUserRole.users currentUser
                    JOIN currentUserRole.workspace currentWorkspace
                    WHERE currentUser.id = {$user->getId()}
                )";
        }

        $dql .= "WHERE ar.parent = :ar_parentid
                AND rt.isVisible = :rt_isvisible";

        if ($isAdmin == false){
            $dql .= " AND arRights.canView = 1";
        }

        if ($resourceTypeId != 0) {
            $dql .= " AND rt.id = :rt_id";
        }

        $query = $this->_em->createQuery($dql);
        $query->setParameter('ar_parentid', $parentId);
        $query->setParameter('rt_isvisible', $isVisible);

        if ($resourceTypeId != 0) {
            $query->setParameter('rt_id', $resourceTypeId);
        }

        $results = array();
        if(!$isAdmin){
            $results = $this->executeQuery($query, $asArray);
        } else {
            $items = $query->iterate(null, AbstractQuery::HYDRATE_ARRAY);
            foreach($items as $key => $item){
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
     * @param User $user Owner of the resources.
     * @param boolean $asArray returns a list of arrays if true, else a list of entities.
     * @return an array of arrays or entities
     */
    public function listRootsForUser(User $user, $asArray = false)
    {
        $dql = "SELECT " . ($asArray ? self::SELECT_FOR_ARRAY : self::SELECT_FOR_ENTITIES)
                . " FROM " . self::FROM_RESOURCES
                . " WHERE ar.parent IS NULL"
                . " AND " . self::WHERECONDITION_USER_WORKSPACE
                . " ORDER BY ar.path";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('u_id', $user->getId());

        return $this->executeQuery($query, $asArray);
    }

    /**
     * Returns an array of all ancestors of a abstractResource
     * (the resource itlsef is returned too).
     * @param listDirectChildrenResourceInstances $resource The resource about which we want ancestors.
     * @return array (name, id)
     */
    public function listAncestors(AbstractResource $resource)
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
     * Returns all resources owned by the user and filtered with given criterias.
     * @param Array $criterias Array of criterias to use to build the filter.
     * @param User $user Owner of the resources.
     * @param boolean $asArray returns a list of arrays if true, else a list of entities.
     * @return an array of arrays or entities
     */
    public function listResourcesForUserWithFilter($criterias, User $user, $asArray = false)
    {
        $isAdmin = false;

        if ($user != null){
            foreach($user->getRoles() as $role){
               if($role == 'ROLE_ADMIN'){
                   $isAdmin = true;
               }
            }
        }

        $dql = "SELECT " . ($asArray ? self::SELECT_FOR_ARRAY : self::SELECT_FOR_ENTITIES)
                . " FROM " . self::FROM_RESOURCES;

        if ($isAdmin == false){
            $dql .= "
                JOIN ar.rights arRights
                JOIN arRights.role rightRole WITH rightRole IN (
                    SELECT currentUserRole FROM Claroline\CoreBundle\Entity\Role currentUserRole
                    JOIN currentUserRole.users currentUser
                    JOIN currentUserRole.workspace currentWorkspace
                    WHERE currentUser.id = {$user->getId()}
                )";
        }

        $dql .= " WHERE rt.isVisible=1"
                . " AND " . self::WHERECONDITION_USER_WORKSPACE;

        if ($user != null && $isAdmin == false){
            $dql .= " AND arRights.canView = 1";
        }

        foreach ($criterias as $key => $value) {
            $methodName = 'build' . ucfirst($key) . 'Filter';
            $dql .= $this->$methodName($key, $value);
        }

        $dql .= " ORDER BY ar.path";

        $query = $this->_em->createQuery($dql);
        $this->bindFilter($query, $criterias, $user);

        return $this->executeQuery($query, $asArray);
    }

    /**
     * Build the Dql part of the filter about Types.
     * @param string $key The name of the filter (eg. "types", "dateTo"...).
     * @param array $criteria Array of values to filter on.
     * @return string
     */
    public function buildTypesFilter($key, $criteria)
    {
        $dqlPart = "";
        $isFirst = true;
        $keys = array_keys($criteria);

        foreach ($keys as $i) {
            if ($isFirst) {
                $dqlPart.= " AND (rt.name = :{$key}{$i}";   // eg. "types0"
                $isFirst = false;
            } else {
                $dqlPart .= " OR rt.name = :{$key}{$i}";
            }
        }
        if (strlen($dqlPart) > 0) {
            $dqlPart .= ")";
        }

        return $dqlPart;
    }

    /**
     * Build the Dql part of the filter about Root.
     * @param string $key The name of the filter (eg. "types", "dateTo"...).
     * @param array $criteria Array of values to filter on.
     * @return string
     */
    public function buildRootsFilter($key, $criteria)
    {
        $dqlPart = "";
        $isFirst = true;
        $keys = array_keys($criteria);

        foreach ($keys as $i) {
            if ($isFirst) {
                $dqlPart.= " AND (ar.path like :{$key}{$i}";
                $isFirst = false;
            } else {
                $dqlPart.= " OR ar.path like :{$key}{$i}";
            }
        }
        if (strlen($dqlPart) > 0) {
            $dqlPart .= ')';
        }

        return $dqlPart;
    }

    /**
     * Build the Dql part of the filter about Mime types.
     * @param string $key The name of the filter (eg. "types", "dateTo"...).
     * @param array $criteria Array of values to filter on.
     * @return string
     */
    public function buildMimeTypesFilter($key, $criteria)
    {
        $dqlPart = "";
        $isFirst = true;
        $keys = array_keys($criteria);

        foreach ($keys as $i) {
            if ($isFirst) {
                $dqlPart.= "AND (ic.type LIKE :{$key}{$i}";
                $isFirst = false;
            } else {
                $dqlPart.= " OR  ic.type LIKE :{$key}{$i}";
            }
        }
        if (strlen($dqlPart) > 0) {
            $dqlPart .= ')';
        }

        return $dqlPart;
    }

    /**
     * Build the Dql part of the filter about FromDate.
     * @param string $key The name of the filter (eg. "types", "dateTo"...).
     * @param array $criteria Array of values to filter on.
     * @return string
     */
    public function buildDateFromFilter($key, $criteria)
    {
        return " AND ar.creationDate >= :{$key}";
    }

    /**
     * Build the Dql part of the filter about ToDate.
     * @param string $key The name of the filter (eg. "types", "dateTo"...).
     * @param array $criteria Array of values to filter on.
     * @return string
     */
    public function buildDateToFilter($key, $criteria)
    {
        return " AND ar.creationDate <= :{$key}";
    }

    /**
     * Build the Dql part of the filter about Name.
     * @param string $key The name of the filter (eg. "types", "dateTo"...).
     * @param array $criteria Array of values to filter on.
     * @return string
     */
    public function buildNameFilter($key, $criteria)
    {
        return " AND ar.name LIKE :{$key}";
    }

    /**
     * Bind all values to the DQL filter.
     * @param Query $query to bind with values.
     * @param Array $criterias Array of criterias to apply.
     * @param User $user Owner of the resources.
     * @return string
     */
    private function bindFilter($query, $criterias, $user)
    {
        // List of filter fields that have multiple values.
        $multipleValues = array('roots' => '', 'types' => '', 'mimeTypes' => '');
        // List of filter fields that must be used with "LIKE" query (%xx%).
        $likeValue = array('name' => '', 'mimeTypes' => '');
        // List of filter fields that must be used with "LIKE" query (%xx%).
        $rootLikeValue = array('roots' => '');

        foreach ($criterias as $key => $value) {
            if (array_key_exists($key, $multipleValues)) {
                $this->bindArray($query, $key, $value, array_key_exists($key, $likeValue), array_key_exists($key, $rootLikeValue));
            } else {
                if (!array_key_exists($key, $likeValue)) {
                    $query->setParameter($key, $value);
                } else {
                    $query->setParameter($key, "%" . $value . "%");
                }
            }
        }

        $query->setParameter('u_id', $user->getId());
    }

    /**
     * Bind all values to the DQL filter.
     * @param Query $query to bind with values.
     * @param string $key The name of the filter (eg. "types", "dateTo"...).
     * @param array $values Array of values to filter on.
     * @param boolean $isLike If true, will bind with a %val%" value.
     * @param boolean $isRootLike If true, will bind with a val%" value.
     * @return string
     */
    private function bindArray($query, $key, $values, $isLike = false, $isRootLike = false)
    {
        foreach ($values as $i => $value) {
            if ($isRootLike === true) {
                $query->setParameter("{$key}{$i}", $value . "_%");
            } else if ($isLike === true) {
                $query->setParameter("{$key}{$i}", "%" . $value . "%");
            } else {
                $query->setParameter("{$key}{$i}", $value);
            }
        }
    }

    /**
     * Execute a DQL query and may return a list of entities or a list of arrays.
     * If it returns arrays, it add a "pathfordisplay" field in each item.
     * @param Query $query The query to execute.
     * @param boolean $asArray Set it to true if you want the result as a list of arrays.
     * @param int $numrows Maximum number of rows to return.
     * @param int ResourceType $resourceType Resource type to filter on.
     * @return array of arrays or array of entities
     */
    private function executeQuery($query, $asArray, $offset = null, $numrows = null)
    {
        $query->setFirstResult($offset);
        $query->setMaxResults($numrows);

        if ($asArray) {
            $res = $query->getArrayResult();
            // Add a field "pathfordisplay" in each entity (as array) of the given array.
            foreach ($res as &$r) {
                $r["pathfordisplay"] = AbstractResource::convertPathForDisplay($r["path"]);
                unset($r['path']);
//                unset($r['parent_id']);
            }
        } else {
            $res = $query->getResult();
        }
        return $res;
    }

}