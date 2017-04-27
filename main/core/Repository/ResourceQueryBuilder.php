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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Repository\Exception\MissingSelectClauseException;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Builder for DQL queries on AbstractResource entities.
 */
class ResourceQueryBuilder
{
    private $joinSingleRelatives;
    private $resultAsArray;
    private $leftJoinRights;
    private $leftJoinLogs;
    private $selectClause;
    private $whereClause;
    private $orderClause;
    private $groupByClause;
    private $joinClause;
    private $parameters;
    private $fromClause;
    private $joinRelativesClause;
    private $leftJoinRoles;
    private $bundles;

    public function init()
    {
        $eol = PHP_EOL;
        $this->fromClause = "FROM Claroline\CoreBundle\Entity\Resource\ResourceNode node{$eol}";
        $this->joinSingleRelatives = true;
        $this->resultAsArray = false;
        $this->leftJoinRights = false;
        $this->leftJoinLogs = false;
        $this->selectClause = null;
        $this->whereClause = null;
        $this->orderClause = null;
        $this->groupByClause = null;
        $this->joinClause = '';
        $this->parameters = [];
        $this->leftJoinRoles = false;
        $this->bundles = [];

        $this->joinRelativesClause = "JOIN node.creator creator{$eol}".
            "JOIN node.resourceType resourceType{$eol}".
            "LEFT JOIN node.parent parent{$eol}".
            "LEFT JOIN node.icon icon{$eol}";
    }

    public function setBundles(array $bundles)
    {
        $this->bundles = $bundles;
        //look at the getDql() method to see where it come from
        $this->addWhereClause('(CONCAT(p.vendorName, p.bundleName) IN (:bundles) OR rtp.plugin is NULL)');
        $this->parameters[':bundles'] = $bundles;
    }

    /**
     * Selects nodes as entities.
     *
     * @param bool   $joinSingleRelatives Whether the creator, type and icon must be joined to the query
     * @param string $class
     *
     * @return ResourceQueryBuilder
     */
    public function selectAsEntity($joinSingleRelatives = false, $class = null)
    {
        $this->init();
        $eol = PHP_EOL;

        if ($class) {
            $this->selectClause = 'SELECT resource'.PHP_EOL;
            $this->fromClause = "FROM {$class} resource{$eol} JOIN resource.resourceNode node{$eol}";
        } else {
            $this->selectClause = 'SELECT node'.PHP_EOL;
        }

        $this->joinSingleRelatives = $joinSingleRelatives;

        return $this;
    }

    /**
     * Selects nodes as arrays. Resource type, creator and icon are always added to the query.
     *
     * @param bool $withMaxPermissions Whether maximum permissions must be calculated and added to the result
     * @param bool $withLastOpenDate
     *
     * @return ResourceQueryBuilder
     */
    public function selectAsArray($withMaxPermissions = false, $withLastOpenDate = false)
    {
        $this->init();
        $this->resultAsArray = true;
        $this->joinSingleRelatives = true;
        $eol = PHP_EOL;
        $this->selectClause =
            "SELECT DISTINCT{$eol}".
            "    node.id as id,{$eol}".
            "    node.name as name,{$eol}".
            "    node.path as path,{$eol}".
            "    parent.id as parent_id,{$eol}".
            "    creator.username as creator_username,{$eol}".
            "    creator.id as creator_id,{$eol}".
            "    resourceType.name as type,{$eol}".
            "    icon.relativeUrl as large_icon,{$eol}".
            "    node.mimeType as mime_type,{$eol}".
            "    node.index as index_dir,{$eol}".
            "    node.creationDate as creation_date,{$eol}".
            "    node.modificationDate as modification_date,{$eol}".
            "    node.published as published,{$eol}".
            "    node.accessibleFrom as accessible_from,{$eol}".
            "    node.accessibleUntil as accessible_until{$eol}";

        if ($withMaxPermissions) {
            $this->leftJoinRights = true;
            $this->selectClause .= ",{$eol}rights.mask";
        }

        if ($withLastOpenDate) {
            $this->leftJoinLogs = true;
            $this->selectClause .= ",{$eol}log.dateLog as last_opened";
        }

        $this->selectClause .= $eol;

        return $this;
    }

    /**
     * Filters nodes belonging to a given workspace.
     *
     * @param Workspace $workspace
     *
     * @return ResourceQueryBuilder
     */
    public function whereInWorkspace(Workspace $workspace)
    {
        $this->addWhereClause('node.workspace = :workspace_id');
        $this->parameters[':workspace_id'] = $workspace->getId();

        return $this;
    }

    public function addLastOpenDate(User $user)
    {
        $this->addWhereClause('log.doer = :doer_id');
        $this->addWhereClause('log_node.id = node.id');
        $this->parameters[':doer_id'] = $user->getId();

        return $this;
    }

    /**
     * Filters nodes that are the immediate children of a given node.
     *
     * @param ResourceNode $parent
     *
     * @return ResourceQueryBuilder
     */
    public function whereParentIs(ResourceNode $parent)
    {
        $this->addWhereClause('node.parent = :ar_parentId');
        $this->parameters[':ar_parentId'] = $parent->getId();

        return $this;
    }

    /**
     * Filters nodes whose path begins with a given path.
     *
     * @param string $path
     * @param bool   $includeGivenPath
     *
     * @return ResourceQueryBuilder
     */
    public function wherePathLike($path, $includeGivenPath = true)
    {
        $this->addWhereClause('node.path LIKE :pathlike');
        $this->parameters[':pathlike'] = $path.'%';

        if (!$includeGivenPath) {
            $this->addWhereClause('node.path <> :path');
            $this->parameters[':path'] = $path;
        }

        return $this;
    }

    /**
     * Filters nodes that are bound to any of the given roles.
     *
     * @param array[string|RoleInterface] $roles
     *
     * @return \Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function whereRoleIn(array $roles)
    {
        if (0 < count($roles)) {
            $this->leftJoinRights = true;
            $eol = PHP_EOL;
            $clause = "{$eol}({$eol}";

            foreach ($roles as $i => $role) {
                $role = $roles[$i] instanceof RoleInterface ? $roles[$i]->getRole() : $roles[$i];
                $clause .= $i > 0 ? '    OR ' : '    ';
                $clause .= "rightRole.name = :role_{$i}{$eol}";
                $this->parameters[":role_{$i}"] = $role;
            }

            $this->addWhereClause($clause.')');
        }

        return $this;
    }

    /**
     * Filters nodes that can be opened.
     *
     * @return ResourceQueryBuilder
     */
    public function whereCanOpen()
    {
        $this->leftJoinRights = true;
        $this->addWhereClause('BIT_AND(rights.mask, 1) = 1');

        return $this;
    }

    /**
     * Filters nodes belonging to any of the workspaces a given user has access to.
     *
     * @param User $user
     *
     * @return ResourceQueryBuilder
     */
    public function whereInUserWorkspace(User $user)
    {
        $eol = PHP_EOL;
        $clause =
            "node.workspace IN{$eol}".
            "({$eol}".
            "    SELECT aw FROM Claroline\CoreBundle\Entity\Workspace\Workspace aw{$eol}".
            "    JOIN aw.roles r{$eol}".
            "    WHERE r IN (SELECT r2 FROM Claroline\CoreBundle\Entity\Role r2 {$eol}".
            "       LEFT JOIN r2.users u {$eol}".
            "       LEFT JOIN r2.groups g {$eol}".
            "       LEFT JOIN g.users u2 {$eol}".
            "       WHERE u.id = :user_id OR u2.id = :user_id {$eol}".
            "   ) {$eol}".
            ") {$eol}";
        $this->addWhereClause($clause);
        $this->parameters[':user_id'] = $user->getId();

        return $this;
    }

    /**
     * Filters nodes of any of the given types.
     *
     * @param array[string] $types
     *
     * @return ResourceQueryBuilder
     */
    public function whereTypeIn(array $types)
    {
        if (count($types) > 0) {
            $this->joinSingleRelatives = true;
            $eol = PHP_EOL;
            $clause = "{$eol}({$eol}";

            for ($i = 0, $count = count($types); $i < $count; ++$i) {
                $clause .= $i === 0 ?
                    "resourceType.name = :type_{$i}" :
                    "OR resourceType.name = :type_{$i}";
                $clause .= $i < $count - 1 ? PHP_EOL : '';
                $this->parameters[":type_{$i}"] = $types[$i];
            }

            $this->addWhereClause($clause.')');
        }

        return $this;
    }

    /**
     * Filters nodes that are the descendants of any of the given root directory paths.
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

            for ($i = 0; $i < $count; ++$i) {
                $clause .= $i > 0 ? '    OR ' : '    ';
                $clause .= "node.path LIKE :root_{$i}{$eol}";
                $this->parameters[":root_{$i}"] = "{$roots[$i]}_%";
            }

            $this->addWhereClause($clause.')');
        }

        return $this;
    }

    /**
     * Filters nodes created at or after a given date.
     *
     * @param string $date
     *
     * @return ResourceQueryBuilder
     */
    public function whereDateFrom($date)
    {
        $this->addWhereClause('node.creationDate >= :dateFrom');
        $this->parameters[':dateFrom'] = $date;

        return $this;
    }

    /**
     * Filters nodes created at or before a given date.
     *
     * @param string $date
     *
     * @return ResourceQueryBuilder
     */
    public function whereDateTo($date)
    {
        $this->addWhereClause('node.creationDate <= :dateTo');
        $this->parameters[':dateTo'] = $date;

        return $this;
    }

    /**
     * Filters nodes whose name contains a given string.
     *
     * @param string $name
     *
     * @return ResourceQueryBuilder
     */
    public function whereNameLike($name)
    {
        $this->addWhereClause('node.name LIKE :name');
        $this->parameters[':name'] = "%{$name}%";

        return $this;
    }

    /**
     * Filters nodes that can or cannot be exported.
     *
     * @param bool $isExportable
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
     * Filters the nodes that don't have a parent (roots).
     *
     * @return ResourceQueryBuilder
     */
    public function whereParentIsNull()
    {
        $this->addWhereClause('node.parent IS NULL');

        return $this;
    }

    public function whereMimeTypeIs($mimeType)
    {
        $this->addWhereClause('node.mimeType LIKE :mimeType');
        $this->parameters[':mimeType'] = $mimeType;

        return $this;
    }

    /**
     * Filters nodes that are published.
     *
     * @param  $user (not typing because we don't want anon. to crash everything)
     *
     * @return ResourceQueryBuilder
     */
    public function whereIsAccessible($user)
    {
        $currentDate = new \DateTime();
        $clause = '(
            creator.id = :creatorId
            OR (
                node.published = true
                AND (node.accessibleFrom IS NULL OR node.accessibleFrom <= :currentdate)
                AND (node.accessibleUntil IS NULL OR node.accessibleUntil >= :currentdate)
            )
        )';
        $this->addWhereClause($clause);
        $this->parameters[':creatorId'] = ($user === 'anon.') ? -1 : $user->getId();
        $this->parameters[':currentdate'] = $currentDate->format('Y-m-d H:i:s');

        return $this;
    }

    /**
     * Orders nodes by path.
     *
     * @return ResourceQueryBuilder
     */
    public function orderByPath()
    {
        $this->orderClause = 'ORDER BY node.path'.PHP_EOL;

        return $this;
    }

    /**
     * Orders nodes by name.
     *
     * @return ResourceQueryBuilder
     */
    public function orderByName()
    {
        $this->orderClause = 'ORDER BY node.name'.PHP_EOL;

        return $this;
    }

    /**
     * Orders nodes by index.
     *
     * @return ResourceQueryBuilder
     */
    public function orderByIndex()
    {
        $this->orderClause = 'ORDER BY node.index'.PHP_EOL;

        return $this;
    }

    /**
     * Groups nodes by id.
     *
     * @return ResourceQueryBuilder
     */
    public function groupById()
    {
        $this->groupByClause = 'GROUP BY node.id'.PHP_EOL;

        return $this;
    }

    public function groupByResourceUserTypeAndIcon()
    {
        $this->groupByClause = '
            GROUP BY node.id,
                     parent.id,
                     previous.id,
                     next.id,
                     creator.username,
                     resourceType.name,
                     icon.relativeUrl
        '.PHP_EOL;

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
        $joinRelatives = $this->joinSingleRelatives ? $this->joinRelativesClause : '';
        $joinRelatives .= " LEFT JOIN node.resourceType rtp{$eol}
            LEFT JOIN rtp.plugin p{$eol}";
        $joinRoles = $this->leftJoinRoles ?
            "LEFT JOIN node.workspace workspace{$eol}".
            "LEFT JOIN workspace.roles role{$eol}" :
            '';
        $joinRights = $this->leftJoinRights ?
            "LEFT JOIN node.rights rights{$eol}".
            "JOIN rights.role rightRole{$eol}" :
            '';
        $joinLogs = $this->leftJoinLogs ?
            "JOIN node.logs log{$eol}".
            "JOIN log.resourceNode log_node{$eol}" :
            '';
        $dql =
            $this->selectClause.
            $this->fromClause.
            $joinRelatives.
            $joinRoles.
            $joinRights.
            $joinLogs.
            $this->joinClause.
            $this->whereClause.
            $this->groupByClause.
            $this->orderClause;

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
    public function addWhereClause($clause)
    {
        if (null === $this->whereClause) {
            $this->whereClause = "WHERE {$clause}".PHP_EOL;
        } else {
            $this->whereClause = $this->whereClause."AND {$clause}".PHP_EOL;
        }
    }

    /**
     * Adds a statement to the query "JOIN" clause.
     *
     * @param string $clause
     */
    public function addJoinClause($clause)
    {
        $this->joinClause = $clause.PHP_EOL;
    }

    /**
     * Filters nodes that are bound to any of the given roles.
     *
     * @param array[string|RoleInterface] $roles
     *
     * @return \Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function whereHasRoleIn(array $roles)
    {
        $managerRoles = [];
        $otherRoles = [];

        foreach ($roles as $role) {
            $roleName = $role instanceof RoleInterface ? $role->getRole() : $role;

            if (preg_match('/^ROLE_WS_MANAGER_/', $roleName)) {
                $managerRoles[] = $roleName;
            } else {
                $otherRoles[] = $roleName;
            }
        }

        $eol = PHP_EOL;

        if (count($otherRoles) > 0 && count($managerRoles) === 0) {
            $this->leftJoinRights = true;
            $clause = "{$eol}({$eol}";
            $clause .= "rightRole.name IN (:roles){$eol}";
            $this->parameters[':roles'] = $otherRoles;
            $clause .= "AND{$eol}BIT_AND(rights.mask, 1) = 1{$eol})";
            $this->addWhereClause($clause);
        } elseif (count($otherRoles) === 0 && count($managerRoles) > 0) {
            $this->leftJoinRoles = true;
            $clause = "{$eol}({$eol}";
            $clause .= "role.name IN (:roles){$eol}";
            $this->parameters[':roles'] = $managerRoles;
            $this->addWhereClause($clause.')');
        } elseif (count($otherRoles) > 0 && count($managerRoles) > 0) {
            $this->leftJoinRoles = true;
            $this->leftJoinRights = true;
            $clause = "{$eol}({$eol}({$eol}";
            $clause .= "rightRole.name IN (:otherroles){$eol}";
            $this->parameters[':otherroles'] = $otherRoles;
            $clause .= "AND{$eol}BIT_AND(rights.mask, 1) = 1{$eol}){$eol}";
            $clause .= "OR{$eol}";
            $clause .= "role.name IN (:managerroles){$eol}";
            $this->parameters[':managerroles'] = $managerRoles;
            $this->addWhereClause($clause.')');
        }

        return $this;
    }

    /**
     * Filters nodes by active value.
     *
     * @param bool $active
     *
     * @return ResourceQueryBuilder
     */
    public function whereActiveIs($active)
    {
        $this->addWhereClause('node.active = :active');
        $this->parameters[':active'] = $active;

        return $this;
    }

    /**
     * Filters nodes by ids.
     *
     * @param array $ids
     *
     * @return ResourceQueryBuilder
     */
    public function whereIdIn($ids)
    {
        $this->addWhereClause('node.id IN (:ids)');
        $this->parameters[':ids'] = $ids;

        return $this;
    }
}
