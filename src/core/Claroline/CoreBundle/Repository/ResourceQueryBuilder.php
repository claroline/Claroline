<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

class ResourceQueryBuilder
{
    private $dql;
    private $isFirstWhereClause;
    private $preparedStatementValue;
    //Count the number of time the whereRole method was used (required for
    //the prepared statement.
    private $whereRoleCounter;

    public function __construct($dql = '')
    {
        $this->dql = $dql;
        $this->isFirstWhereClause = true;
        $this->preparedStatementValue = array();
        $this->whereRoleCounter = 0;
    }

    /** SELECT DQL part to get entities. */
    //  Technical note:
    //      Selecting "ar" is needed to force Doctrine to load both entities
    //      at the same time (same SQL request) else doctrine will make N requests
    //      to get "ar" information.
    //      That's also the reason why we do not use
    //      the "MaterializedPathRepository->getChildren" method.

    /**
     * Basic select.
     *
     * @return \Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function select()
    {
        $this->dql .= "SELECT DISTINCT
            ar.id as id,
            ar.name as name,
            ar.path as path,
            IDENTITY(ar.parent) as parent_id,
            aru.username as creator_username,
            rt.name as type,
            rt.isBrowsable as is_browsable,
            ic.relativeUrl as large_icon";

        return $this;
    }

    /**
     * Adds the resource permissions to the basic select.
     *
     * @return \Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function selectPermissions()
    {
         $this->dql .= ', MAX (arRights.canExport) as can_export'
             . ', MAX (arRights.canDelete) as can_delete'
             . ', MAX (arRights.canEdit) as can_edit';

         return $this;
    }

    /**
     * From (use it with select()).
     *
     * @return \Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function from()
    {
        $this->dql .= " FROM Claroline\CoreBundle\Entity\Resource\AbstractResource ar
            JOIN ar.creator aru
            JOIN ar.resourceType rt
            JOIN ar.icon ic";

        return $this;
    }

    /**
     * Adds the where clause.
     *
     * @return \Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function where()
    {
        $this->dql .= " WHERE ";

        return $this;
    }

    /**
     * Adds a clause.
     *
     * @return \Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function addClause($clause)
    {
        $clause = strtoupper($clause);
        if ($clause === 'OR') {
            $this->isFirstWhereClause = true;
        }

        $this->dql .= " {$clause} ";

        return $this;
    }

    /**
     * Join on the user rights (and roles).
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function joinRightsForUser(User $user)
    {
        $this->dql .= "
            JOIN ar.rights arRights
            JOIN arRights.role rightRole WITH rightRole IN (
                SELECT currentUserRole FROM Claroline\CoreBundle\Entity\Role currentUserRole
                JOIN currentUserRole.users currentUser
                WHERE currentUser.id = {$user->getId()}
            ) ";

        return $this;
    }

    public function leftJoinOnRightsAndRole()
    {
        $this->dql .= " LEFT JOIN ar.rights arRights JOIN arRights.role rightRole ";

        return $this;
    }

    public function whereIsVisible()
    {
        if (!$this->isFirstWhereClause) {
            $this->dql .= ' AND ';
        }

        $this->dql .= " rt.isVisible = 1 ";
        $this->isFirstWhereClause = false;

        return $this;
    }

    public function whereInUserWorkspace(User $user)
    {
        if (!$this->isFirstWhereClause) {
            $this->dql .= ' AND ';
        }

        $this->dql .= " ar.workspace IN
            (
                SELECT aw FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace aw
                JOIN aw.roles r
                JOIN r.users u
                WHERE u.id = :u_id
            ) ";

        $this->preparedStatementValue[':u_id'] = $user->getId();
        $this->isFirstWhereClause = false;

        return $this;
    }

    /**
     * Requires a join on the rights.
     *
     * @return \Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function whereCanOpen()
    {
        if (!$this->isFirstWhereClause) {
            $this->dql .= ' AND ';
        }

        $this->dql .= ' arRights.canOpen = 1 ';
        $this->isFirstWhereClause = false;

        return $this;
    }

    /**
     * Requires a join on the rights.
     * whereRoleCounter = temporary (?) fix if the method is used many time
     * inside a loop (buildForRolesChildrenQuery)
     *
     * @param string $roleName

     * @return \Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function whereRole($roleName)
    {
        if (!$this->isFirstWhereClause) {
            $this->dql .= ' AND ';
        }

         $this->dql .= "rightRole.name LIKE :role_name{$this->whereRoleCounter}";
         $this->isFirstWhereClause = false;
         $this->preparedStatementValue[":role_name{$this->whereRoleCounter}"] = $roleName;
         $this->whereRoleCounter++;

         return $this;
    }

    public function whereParent(AbstractResource $parent)
    {
        if (!$this->isFirstWhereClause) {
            $this->dql .= ' AND ';
        }

        $this->dql .= ' ar.parent = :ar_parentId ';
        $this->isFirstWhereClause = false;
        $this->preparedStatementValue[':ar_parentId'] = $parent->getId();

        return $this;
    }

    public function whereParentIsNull()
    {
        if (!$this->isFirstWhereClause) {
            $this->dql .= ' AND ';
        }

        $this->dql .= ' ar.parent IS NULL ';
        $this->isFirstWhereClause = false;

        return $this;
    }

    public function getDql()
    {
        return $this->dql;
    }

    public function setFirstWhereClause($bool)
    {
        $this->isFirstWhereClause = $bool;
    }

    /**
     * Build the Dql part of the filter about Types.
     *
     * @param array $types the types name;
     *
     * @return \Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function whereTypes(array $types)
    {
        if (!$this->isFirstWhereClause) {
            $this->dql .= ' AND ';
        }

        $isFirst = true;
        $dqlPart = "";

        for ($i = 0, $count = count($types); $i < $count; $i++) {
            if ($isFirst) {
                $dqlPart .= " (rt.name = :types{$i}";   // eg. "types0"
                $isFirst = false;
            } else {
                $dqlPart .= " OR rt.name = :types{$i}";
            }

            $this->preparedStatementValue[":types{$i}"] = $types[$i];
        }
        if (strlen($dqlPart) > 0) {
            $dqlPart .= ") ";
        }

        $this->dql .= $dqlPart;
        $this->isFirstWhereClause = false;

        return $this;
    }

    /**
     * Build the Dql part of the filter about Root.
     *
     * @param array $roots the path of the $roots
     *
     * @return \Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function whereRoots(array $roots)
    {
        if (!$this->isFirstWhereClause) {
            $this->dql .= ' AND ';
        }

        $dqlPart = "";
        $isFirst = true;

        for ($i = 0, $count = count($roots); $i < $count; $i++) {
            if ($isFirst) {
                $dqlPart .= "(ar.path like :roots{$i} ";
                $isFirst = false;
            } else {
                $dqlPart .= " OR ar.path like :roots{$i} ";
            }
            $this->preparedStatementValue[":roots{$i}"] = "{$roots[$i]}%";
        }
        if (strlen($dqlPart) > 0) {
            $dqlPart .= ')';
        }

        $this->dql .= $dqlPart;
        $this->isFirstWhereClause = false;

        return $this;
    }

    /**
     *
     * Build the Dql part of the filter about FromDate.
     *
     * @param string $date
     *
     * @return \Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function whereDateFrom($date)
    {
        if (!$this->isFirstWhereClause) {
            $this->dql .= ' AND ';
        }

        $this->dql .= " ar.creationDate >= :dateFrom ";
        $this->preparedStatementValue[':dateFrom'] = $date;
        $this->isFirstWhereClause = false;

        return $this;
    }

    /**
     * Build the Dql part of the filter about ToDate.
     *
     * @param string $date
     *
     * @return \Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function whereDateTo($date)
    {
        if (!$this->isFirstWhereClause) {
            $this->dql .= ' AND ';
        }

        $this->dql .= " ar.creationDate <= :dateTo ";
        $this->preparedStatementValue[':dateTo'] = $date;
        $this->isFirstWhereClause = false;

        return $this;
    }



    /**
     * Build the Dql part of the filter about Name.
     *
     * @param string $name
     *
     * @return \Claroline\CoreBundle\Repository\ResourceQueryBuilder
     */
    public function whereName($name)
    {
        if (!$this->isFirstWhereClause) {
            $this->dql .= ' AND ';
        }

        $this->dql .= " ar.name LIKE :name ";
        $this->preparedStatementValue[':name'] = "%{$name}%";
        $this->isFirstWhereClause = false;

        return $this;
    }

    public function wherePath($path, $includeFirstNode)
    {
        if (!$this->isFirstWhereClause) {
            $this->dql .= ' AND ';
        }
        $startNodeClause = '';
        if (!$includeFirstNode) {
            $startNodeClause = 'AND ar.path <> :path';
            $this->preparedStatementValue[':path'] = $path;
        }
        $this->dql .= "(ar.path LIKE :pathlike {$startNodeClause})";
        $this->preparedStatementValue[':pathlike'] = "{$path}%";
        $this->isFirstWhereClause = false;

        return $this;
    }

    public function orderByPath()
    {
        $this->dql .= ' ORDER BY ar.path ';

        return $this;
    }

    public function groupById()
    {
        $this->dql .= ' GROUP BY ar.id ';

        return $this;
    }

    public function setQueryParameters(\Doctrine\ORM\Query $query)
    {
        foreach ($this->preparedStatementValue as $key => $value) {
            $query->setParameter($key, $value);
        }

        return $query;
    }
}
