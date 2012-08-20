<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/*
 * Lists of usefull common platform methods
 * getDateFormatString
 * getTimeFormatString
 * getIsNullExpression
 * getIsNotNullExpression
 * getWildCards
 * getCountExpression
 */
class ResourceInstanceRepository extends NestedTreeRepository
{
    const SELECT_INSTANCE = "SELECT
            ri.id as id,
            ri.name as name,
            ri.created as created,
            ri.updated as updated,
            ri.lft as lft,
            ri.lvl as lvl,
            ri.rgt as rgt,
            ri.root as root,
            ri.parent_id as parent_id,
            ri.workspace_id as workspace_id,
            ri.resource_id as resource_id,
            uri.id as instance_creator_id,
            uri.username as instance_creator_username,
            ures.id as resource_creator_id,
            ures.username as resource_creator_username,
            rt.id as resource_type_id,
            rt.type as type,
            rt.is_navigable as is_navigable,
            rt.icon as icon,
            rt.thumbnail as thumbnail
            FROM claro_resource_instance ri
            INNER JOIN  claro_user uri
            ON uri.id = ri.user_id
            INNER JOIN claro_resource res
            ON res.id = ri.resource_id
            INNER JOIN claro_resource_type rt
            ON res.resource_type_id = rt.id
            INNER JOIN claro_user ures
            ON res.user_id = ures.id";

    const SELECT_USER_WORKSPACES_ID = "SELECT
            cw.id FROM claro_workspace cw
            INNER JOIN claro_role cr
            ON cr.workspace_id = cw.id
            INNER JOIN claro_user_role cur
            ON cur.role_id = cr.id
            INNER JOIN claro_user cu
            ON cu.id = cur.user_id
            WHERE cu.id = :userId
        ";

    public function getWSListableRootResource(AbstractWorkspace $ws)
    {
        $dql = "
            SELECT re FROM Claroline\CoreBundle\Entity\Resource\ResourceInstance re
            WHERE re.lvl = 0
            AND re.workspace = {$ws->getId()}
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getListableChildren(ResourceInstance $resourceInstance, $resourceTypeId = 0)
    {
        $dql = "
            SELECT ri FROM Claroline\CoreBundle\Entity\Resource\ResourceInstance ri
            JOIN ri.parent par
            JOIN ri.abstractResource res
            WHERE par.id = {$resourceInstance->getId()}
            AND res.resourceType IN
            (
                SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
                WHERE rt.isListable = 1";

        if ($resourceTypeId != 0) {
            $dql.= "AND rt.id = {$resourceTypeId}";
        }
        $dql.=')';

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Gets every instance in every workspace the is registered.
     *
     * @param ResourceType $resourceType
     * @param User $user
     *
     * @return array
     */
    public function getInstanceList(User $user, ResourceType $resourceType = null)
    {
        $sql = self::SELECT_INSTANCE."
            WHERE ri.workspace_id IN
            (".self::SELECT_USER_WORKSPACES_ID.")";
        if ($resourceType === null) {
            $sql.="AND rt.type !='directory'";
        } else {
            $sql.="AND rt.id = {$resourceType->getId()}";
        }

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->bindValue('userId', $user->getId());
        $stmt->execute();

        $instances = array();

        while ($row = $stmt->fetch()) {
            $instances[$row['id']] = $row;
        }

        return $instances;
    }



    public function findInstancesFromType(ResourceType $resourceType, User $user)
    {
        $dql = "
            SELECT ri FROM Claroline\CoreBundle\Entity\Resource\ResourceInstance ri
            JOIN ri.abstractResource ar
            JOIN ar.resourceType rt
            WHERE rt.type = '{$resourceType->getType()}'
            AND ri.workspace IN
            (
                SELECT w FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
                JOIN w.roles wr JOIN wr.users u WHERE u.id = '{$user->getId()}'
            )
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }


    public function getChildrenInstanceList(ResourceInstance $resourceInstance, ResourceType $resourceType)
    {
        $sql = self::SELECT_INSTANCE."
            WHERE ri.lft > :left
            AND ri.rgt < :right
            AND rt.type = :type
            ";

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->bindValue('left', $resourceInstance->getLft());
        $stmt->bindValue('right', $resourceInstance->getRgt());
        $stmt->bindValue('type', $resourceType->getType());
        $stmt->execute();
        $instances = array();

        while ($row = $stmt->fetch()) {
            $instances[$row['id']] = $row;
        }

        return $instances;
    }

    public function getChildrenNodes($parentId, $resourceTypeId = 0, $isListable = true)
    {
        $sql = self::SELECT_INSTANCE."
            WHERE ri.parent_id = :parentId
            AND rt.is_listable = :isListable
            ";
            if ($resourceTypeId != 0) {
                $sql .= "AND rt.id = :resourceTypeId";
            }

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->bindValue('parentId', $parentId);
        $stmt->bindValue('isListable', $isListable);
        if($resourceTypeId != 0) {
            $stmt->bindValue('resourceTypeId', $resourceTypeId);
        }
        $stmt->execute();

        $instances = array();

        while ($row = $stmt->fetch()) {
            $instances[$row['id']] = $row;
        }

        return $instances;
    }

    public function getRoots($user)
    {
        $platform = $this->_em->getConnection()->getDatabasePlatform();
        $isNull = $platform->getIsNullExpression('ri.parent_id');

        $sql = self::SELECT_INSTANCE."
            WHERE {$isNull}
            AND ri.workspace_id IN(".self::SELECT_USER_WORKSPACES_ID.")";

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->bindValue('userId', $user->getId());
        $stmt->execute();

       $instances = array();

        while ($row = $stmt->fetch()) {
            $instances[$row['id']] = $row;
        }

        return $instances;
    }

    //count non directory instances for a user.
    public function countInstancesForUser($user)
    {

        $platform = $this->_em->getConnection()->getDatabasePlatform();
        $count = $platform->getCountExpression('ri.id');

        $sql = "
            SELECT {$count} as count FROM claro_resource_instance ri
            INNER JOIN  claro_user uri
            ON uri.id = ri.user_id
            INNER JOIN claro_resource res
            ON res.id = ri.resource_id
            INNER JOIN claro_resource_type rt
            ON res.resource_type_id = rt.id
            IN (" .
            self::SELECT_USER_WORKSPACES_ID . ")
            WHERE rt.type != 'directory'";

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->bindValue('userId', $user->getId());
        $stmt->execute();
        $results = $stmt->fetchAll();

        return $results[0]['count'];
    }

    public function filter($criterias, $user)
    {
        $whereType = '';
        $whereRoot = '';
        $whereDateFrom = '';
        $whereDateTo = '';

        foreach ($criterias as $key => $value) {

            switch($key){
                case 'roots': $whereRoot = $this->filterWhereRoot($key, $value);
                    break;
                case 'types': $whereType = $this->filterWhereType($key, $value);
                    break;
                case 'dateTo': $whereDateTo = $this->filterWhereDateTo($key);
                    break;
                case 'dateFrom': $whereDateFrom = $this->filterWhereDateFrom($key);
                    break;
                default:
                    break;
            }
        }

        $sql = self::SELECT_INSTANCE . "
            WHERE rt.is_listable = 1
            AND rt.type != 'directory'
            AND ri.workspace_id IN(".self::SELECT_USER_WORKSPACES_ID.")"
            .$whereType.$whereRoot.$whereDateTo.$whereDateFrom;

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->bindValue('userId', $user->getId());

        foreach ($criterias as $key => $value) {
            switch($key){
                case 'roots': $this->bindArray($stmt, $key, $value);
                    break;
                case 'types': $this->bindArray($stmt, $key, $value);
                    break;
                case 'dateTo': $stmt->bindValue($key, $criteria);
                    break;
                case 'dateFrom': $stmt->bindValue($key, $criteria);
                    break;
                default:
                    break;
            }
        }

        $stmt->execute();

        $instances = array();

        while ($row = $stmt->fetch()) {
            $instances[$row['id']] = $row;
        }

        return $instances;
    }

    private function filterWhereType($key, $criteria)
    {
        $string = '';
        $i = 0;

        foreach ($criteria as $i => $item) {
            if ($i == 0) {
                $string.= " AND (rt.type = :{$key}{$i}";
                $i++;
            } else {
                $string .= " OR rt.type = :{$key}{$i}";
            }
        }

        $string .= ')';

        return $string;
    }

    private function filterWhereRoot($key, $criteria)
    {
        $string = '';
        $i = 0;

        foreach ($criteria as $i => $item) {
            if ($i == 0) {
                $string.= " AND (ri.root =:{$key}{$i}";
                $i++;
            } else {
                $string.= " OR ri.root = :{$key}{$i}";
            }
        }

        $string .= ')';

        return $string;
    }

   private function filterWhereDateFrom($key)
   {
       $string = '';
       $string.=" AND ri.created >= :{$key}";

       return $string;
   }

   private function filterWhereDateTo($key)
   {
       $string = '';
       $string.=" AND ri.created <= :{$key}";

       return $string;
   }

   private function bindArray($stmt, $key, $criteria)
   {
       foreach ($criteria as $i => $item) {
           $stmt->bindValue("{$key}{$i}", $item);
       }
   }
}