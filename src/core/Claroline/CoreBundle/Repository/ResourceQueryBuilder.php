<?php

namespace Claroline\CoreBundle\Repository;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Repository\Exception\MissingSelectClauseException;

/**
 * Builder for DQL queries on AbstractResource entities.
 */
class ResourceQueryBuilder
{
    private $joinSingleRelatives = true;
    private $resultAsArray = false;
    private $leftJoinRights = false;
    private $selectClause;
    private $whereClause;
    private $orderClause;
    private $groupByClause;
    private $parameters = array();

    /**
     * Selects resources as entities.
     *
     * @param boolean $joinSingleRelatives Whether the creator, type and icon must be joined to the query
     *
     * @return ResourceQueryBuilder
     */
    public function selectAsEntity($joinSingleRelatives = false)
    {
        $this->joinSingleRelatives = $joinSingleRelatives;
        $this->selectClause = 'SELECT resource' . PHP_EOL;

        return $this;
    }

    /**
     * Selects resources as arrays. Resource type, creator and icon are always added to the query.
     *
     * @param boolean $withMaxPermissions Whether maximum permissions must be calculated and added to the result
     *
     * @return ResourceQueryBuilder
     */
    public function selectAsArray($withMaxPermissions = false)
    {
        $this->resultAsArray = true;
        $this->joinSingleRelatives = true;
        $eol = PHP_EOL;
        $this->selectClause =
            "SELECT DISTINCT{$eol}" .
            "    resource.id as id,{$eol}" .
            "    resource.name as name,{$eol}" .
            "    resource.path as path,{$eol}" .
            "    IDENTITY(resource.parent) as parent_id,{$eol}" .
            "    creator.username as creator_username,{$eol}" .
            "    resourceType.name as type,{$eol}" .
            "    resourceType.isBrowsable as is_browsable,{$eol}" .
            "    icon.relativeUrl as large_icon";

        if ($withMaxPermissions) {
            $this->leftJoinRights = true;
            $this->selectClause .=
                ",{$eol}" .
                "    MAX (CASE rights.canExport WHEN true THEN 1 ELSE 0 END) as can_export,{$eol}" .
                "    MAX (CASE rights.canDelete WHEN true THEN 1 ELSE 0 END) as can_delete,{$eol}" .
                "    MAX (CASE rights.canEdit WHEN true THEN 1 ELSE 0 END) as can_edit";
        }

        $this->selectClause .= $eol;

        return $this;
    }

    /**
     * Filters resources belonging to a given workspace.
     *
     * @param AbstractWorkspace $workspace
     *
     * @return ResourceQueryBuilder
     */
    public function whereInWorkspace(AbstractWorkspace $workspace)
    {
        $this->addWhereClause('resource.workspace = :workspace_id');
        $this->parameters[':workspace_id'] = $workspace->getId();

        return $this;
    }

    /**
     * Filters resources that are the immediate children of a given resource.
     *
     * @param AbstractResource $parent
     *
     * @return ResourceQueryBuilder
     */
    public function whereParentIs(AbstractResource $parent)
    {
        $this->addWhereClause('resource.parent = :ar_parentId');
        $this->parameters[':ar_parentId'] = $parent->getId();

        return $this;
    }

    /**
     * Filters resources whose path begins with a given path.
     *
     * @param string    $path
     * @param boolean   $includeGivenPath
     *
     * @return ResourceQueryBuilder
     */
    public function wherePathLike($path, $includeGivenPath = true)
    {
        $this->addWhereClause('resource.path LIKE :pathlike');
        $this->parameters[':pathlike'] = $path . '%';

        if (!$includeGivenPath) {
            $this->addWhereClause('resource.path <> :path');
            $this->parameters[':path'] = $path;
        }

        return $this;
    }

    /**
     * Filters resources that are bound to any of the given roles.
     *
     * @param array[string|RoleInterface] $roles
     *
     * @return \Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function whereRoleIn(array $roles)
    {
        if (0 < $count = count($roles)) {
            $this->leftJoinRights = true;
            $eol = PHP_EOL;
            $clause = "{$eol}({$eol}";

            for ($i = 0; $i < $count; ++$i) {
                $role = $roles[$i] instanceof RoleInterface ? $roles[$i]->getRole() : $roles[$i];
                $clause .= $i > 0 ? '    OR ' : '    ';
                $clause .= "rightRole.name = :role_{$i}{$eol}";
                $this->parameters[":role_{$i}"] = $role;
            }

            $this->addWhereClause($clause . ')');
        }

        return $this;
    }

    /**
     * Filters resources that can be opened.
     *
     * @return ResourceQueryBuilder
     */
    public function whereCanOpen()
    {
        $this->leftJoinRights = true;
        $this->addWhereClause('rights.canOpen = true');

        return $this;
    }

    /**
     * Filters resources belonging to any of the workspaces a given user has access to.
     *
     * @param User $user
     *
     * @return ResourceQueryBuilder
     */
    public function whereInUserWorkspace(User $user)
    {
        $eol = PHP_EOL;
        $clause =
            "resource.workspace IN{$eol}" .
            "({$eol}" .
            "    SELECT aw FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace aw{$eol}" .
            "    JOIN aw.roles r{$eol}" .
            "    JOIN r.users u{$eol}" .
            "    WHERE u.id = :user_id{$eol}" .
            ")";
        $this->addWhereClause($clause);
        $this->parameters[':user_id'] = $user->getId();

        return $this;
    }

    /**
     * Filters resources of any of the given types.
     *
     * @param array[string] $types
     *
     * @return ResourceQueryBuilder
     */
    public function whereTypeIn(array $types)
    {
        if (count($types) > 0) {
            $this->joinSingleRelatives = true;
            $clause = '';

            for ($i = 0, $count = count($types); $i < $count; $i++) {
                $clause .= $i === 0 ?
                    "resourceType.name = :type_{$i}" :
                    "OR resourceType.name = :type_{$i}";
                $clause .= $i < $count - 1 ? PHP_EOL : '';
                $this->parameters[":type_{$i}"] = $types[$i];
            }

            $this->addWhereClause($clause);
        }

        return $this;
    }

    /**
     * Filters resources that are the descendants of any of the given root directory paths.
     *
     * @param array[string] $roots
     *
     * @return ResourceQueryBuilder
     */
    public function whereRootIn(array $roots)
    {
        if (0 !== $count = count($roots)) {
            $eol = PHP_EOL;
            $clause = "{$eol}({$eol}";

            for ($i = 0; $i < $count; $i++) {
                $clause .= $i > 0 ? '    OR ' : '    ';
                $clause .= "resource.path LIKE :root_{$i}{$eol}";
                $this->parameters[":root_{$i}"] = "{$roots[$i]}%";
            }

            $this->addWhereClause($clause . ')');
        }

        return $this;
    }

    /**
     * Filters resources created at or after a given date.
     *
     * @param string $date
     *
     * @return ResourceQueryBuilder
     */
    public function whereDateFrom($date)
    {
        $this->addWhereClause('resource.creationDate >= :dateFrom');
        $this->parameters[':dateFrom'] = $date;

        return $this;
    }

    /**
     * Filters resources created at or before a given date.
     *
     * @param string $date
     *
     * @return ResourceQueryBuilder
     */
    public function whereDateTo($date)
    {
        $this->addWhereClause('resource.creationDate <= :dateTo');
        $this->parameters[':dateTo'] = $date;

        return $this;
    }

    /**
     * Filters resources whose name contains a given string.
     *
     * @param string $name
     *
     * @return ResourceQueryBuilder
     */
    public function whereNameLike($name)
    {
        $this->addWhereClause('resource.name LIKE :name');
        $this->parameters[':name'] = "%{$name}%";

        return $this;
    }

    /**
     * Filters resources that can or cannot be exported.
     *
     * @param boolean $isExportable
     *
     * @return ResourceQueryBuilder
     */
    public function whereIsExportable($isExportable)
    {
        $this->joinSingleRelatives = true;
        $this->addWhereClause('resourceType.isExportable = :isExportable');
        $this->parameters[':isExportable'] = $isExportable;

        return $this;
    }

    /**
     * Filters the resources that don't have a parent (roots).
     *
     * @return Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function whereParentIsNull()
    {
        $this->addWhereClause('resource.parent IS NULL');

        return $this;
    }

    /**
     * Orders resources by path.
     *
     * @return Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function orderByPath()
    {
        $this->orderClause = 'ORDER BY resource.path' . PHP_EOL;

        return $this;
    }

    /**
     * Orders resources by name.
     *
     * @return Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function orderByName()
    {
        $this->orderClause = 'ORDER BY resource.name' . PHP_EOL;

        return $this;
    }

    /**
     * Groups resources by id.
     *
     * @return ResourceQueryBuilder
     */
    public function groupById()
    {
        $this->groupByClause = 'GROUP BY resource.id' . PHP_EOL;

        return $this;
    }

    public function groupByResourceUserTypeAndIcon()
    {
        $this->groupByClause = '
            GROUP BY resource.id,
                     creator.username,
                     resourceType.name,
                     resourceType.isBrowsable,
                     icon.relativeUrl
        ' . PHP_EOL;

        return $this;
    }

    /**
     * Returns the dql query string.
     *
     * @return string
     *
     * @throws MissingSelectClauseException if no select method was previously called
     */
    public function getDql()
    {
        if (null === $this->selectClause) {
            throw new MissingSelectClauseException('Select clause is missing');
        }

        $eol = PHP_EOL;
        $joinRelatives = $this->joinSingleRelatives ?
            "JOIN resource.creator creator{$eol}" .
            "JOIN resource.resourceType resourceType{$eol}" .
            "JOIN resource.icon icon{$eol}" :
            '';
        $joinRights = $this->leftJoinRights ?
            "LEFT JOIN resource.rights rights{$eol}" .
            "JOIN rights.role rightRole{$eol}" :
            '';
        $dql =
            $this->selectClause .
            "FROM Claroline\CoreBundle\Entity\Resource\AbstractResource resource{$eol}" .
            $joinRelatives .
            $joinRights .
            $this->whereClause .
            $this->orderClause .
            $this->groupByClause;

        return $dql;
    }

    /**
     * Returns the parameters used when building the query.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Adds a statement to the query "WHERE" clause.
     *
     * @param string $clause
     */
    private function addWhereClause($clause)
    {
        if (null === $this->whereClause) {
            $this->whereClause = "WHERE {$clause}" . PHP_EOL;
        } else {
            $this->whereClause = $this->whereClause . "AND {$clause}" . PHP_EOL;
        }
    }
}