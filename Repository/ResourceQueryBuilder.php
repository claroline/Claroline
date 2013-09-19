<?php

namespace Claroline\CoreBundle\Repository;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
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
    private $fromClause;
    private $joinRelativesClause;

    public function __construct()
    {
        $eol = PHP_EOL;
        $this->fromClause = "FROM Claroline\CoreBundle\Entity\Resource\ResourceNode node{$eol}";

        $this->joinRelativesClause = "JOIN node.creator creator{$eol}" .
            "JOIN node.resourceType resourceType{$eol}" .
            "LEFT JOIN node.next next{$eol}" .
            "LEFT JOIN node.previous previous{$eol}" .
            "LEFT JOIN node.parent parent{$eol}" .
            "LEFT JOIN node.icon icon{$eol}";
    }

    /**
     * Selects nodes as entities.
     *
     * @param boolean $joinSingleRelatives Whether the creator, type and icon must be joined to the query
     *
     * @return ResourceQueryBuilder
     */
    public function selectAsEntity($joinSingleRelatives = false, $class = null)
    {
        $eol = PHP_EOL;

        if ($class) {
            $this->selectClause = 'SELECT resource' . PHP_EOL;
            $this->fromClause = "FROM {$class} resource{$eol} JOIN resource.resourceNode node{$eol}";
        } else {
            $this->selectClause = 'SELECT node' . PHP_EOL;
        }

        $this->joinSingleRelatives = $joinSingleRelatives;

        return $this;
    }

    /**
     * Selects nodes as arrays. Resource type, creator and icon are always added to the query.
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
            "    node.id as id,{$eol}" .
            "    node.name as name,{$eol}" .
            "    node.path as path,{$eol}" .
            "    parent.id as parent_id,{$eol}" .
            "    creator.username as creator_username,{$eol}" .
            "    resourceType.name as type,{$eol}" .
            "    previous.id as previous_id,{$eol}" .
            "    next.id as next_id,{$eol}" .
            "    icon.relativeUrl as large_icon,{$eol}".
            "    node.mimeType as mime_type";

        if ($withMaxPermissions) {
            $this->leftJoinRights = true;
            $this->selectClause .=
                    ",{$eol}rights.mask";
        }

        $this->selectClause .= $eol;

        return $this;
    }

    /**
     * Filters nodes belonging to a given workspace.
     *
     * @param AbstractWorkspace $workspace
     *
     * @return ResourceQueryBuilder
     */
    public function whereInWorkspace(AbstractWorkspace $workspace)
    {
        $this->addWhereClause('node.workspace = :workspace_id');
        $this->parameters[':workspace_id'] = $workspace->getId();

        return $this;
    }

    /**
     * Filters nodes that are the immediate children of a given node.
     *
     * @param AbstractResource $parent
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
     * @param string  $path
     * @param boolean $includeGivenPath
     *
     * @return ResourceQueryBuilder
     */
    public function wherePathLike($path, $includeGivenPath = true)
    {
        $this->addWhereClause('node.path LIKE :pathlike');
        $this->parameters[':pathlike'] = $path . '%';

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
            "node.workspace IN{$eol}" .
            "({$eol}" .
            "    SELECT aw FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace aw{$eol}" .
            "    JOIN aw.roles r{$eol}" .
            "    WHERE r IN (SELECT r2 FROM Claroline\CoreBundle\Entity\Role r2 {$eol}". 
            "       LEFT JOIN r2.users u {$eol}" .
            "       LEFT JOIN r2.groups g {$eol}" .
            "       LEFT JOIN g.users u2 {$eol}" .
            "       WHERE u.id = :user_id OR u2.id = :user_id {$eol}" .
            "   ) {$eol}" .
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

            for ($i = 0; $i < $count; $i++) {
                $clause .= $i > 0 ? '    OR ' : '    ';
                $clause .= "node.path LIKE :root_{$i}{$eol}";
                $this->parameters[":root_{$i}"] = "{$roots[$i]}%";
            }

            $this->addWhereClause($clause . ')');
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
     * Filters nodes that are shortcuts and selects their target.
     *
     * @return ResourceQueryBuilder
     */
    public function whereIsShortcut()
    {
        $eol = PHP_EOL;
        $this->joinRelativesClause = "JOIN rs.resourceNode node{$eol}" . $this->joinRelativesClause;
        $this->joinRelativesClause = "JOIN rs.target target{$eol}" . $this->joinRelativesClause;
        $this->fromClause = "FROM Claroline\CoreBundle\Entity\Resource\ResourceShortcut rs{$eol}";
        $this->selectClause .= ", target.id as target_id{$eol}";
        $this->selectClause .= ", target.path as target_path{$eol}";

        return $this;
    }

    /**
     * Filters the nodes that don't have a parent (roots).
     *
     * @return Claroline\CoreBundle\Repository\ResourceQueryBuilder
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
     * Orders nodes by path.
     *
     * @return Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function orderByPath()
    {
        $this->orderClause = 'ORDER BY node.path' . PHP_EOL;

        return $this;
    }

    /**
     * Orders nodes by name.
     *
     * @return Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function orderByName()
    {
        $this->orderClause = 'ORDER BY node.name' . PHP_EOL;

        return $this;
    }

    /**
     * Groups nodes by id.
     *
     * @return ResourceQueryBuilder
     */
    public function groupById()
    {
        $this->groupByClause = 'GROUP BY node.id' . PHP_EOL;

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
        $joinRelatives = $this->joinSingleRelatives ? $this->joinRelativesClause: '';
        $joinRights = $this->leftJoinRights ?
            "LEFT JOIN node.rights rights{$eol}" .
            "JOIN rights.role rightRole{$eol}" :
            '';
        $dql =
            $this->selectClause .
            $this->fromClause.
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
