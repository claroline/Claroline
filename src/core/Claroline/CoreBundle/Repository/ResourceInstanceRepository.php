<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class ResourceInstanceRepository extends NestedTreeRepository
{
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

    public function getInstanceList(ResourceType $resourceType, User $user)
    {
        $dql = "
            SELECT ri FROM Claroline\CoreBundle\Entity\Resource\ResourceInstance ri
            JOIN ri.abstractResource ar
            JOIN ar.resourceType rt
            WHERE rt.type = '{$resourceType->getType()}'
            AND ri.workspace IN
            (
                SELECT w FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
                JOIN w.roles wr
                JOIN wr.users u
                WHERE u.id = '{$user->getId()}'
            )
            ORDER BY ar.name
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
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
        $sql = "
            SELECT
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
            rt.icon as icon
            FROM claro_resource_instance ri
            INNER JOIN  claro_user uri
            ON uri.id = ri.user_id
            INNER JOIN claro_resource res
            ON res.id = ri.resource_id
            INNER JOIN claro_resource_type rt
            ON res.resource_type_id = rt.id
            INNER JOIN claro_user ures
            ON res.user_id = ures.id
            WHERE ri.lft > {$resourceInstance->getLft()}
            AND ri.rgt < {$resourceInstance->getRgt()}
            AND rt.type = '{$resourceType->getType()}'
            ";

        $result = $this->_em
            ->getConnection()
            ->query($sql);

        $instances = array();

        while ($row = $result->fetch()) {
            $instances[$row['id']] = $row;
        }

        return $instances;
    }

    public function getChildrenNodes($parent, $resourceTypeId = 0, $isListable = true)
    {
        $sql = "
            SELECT
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
            rt.icon as icon
            FROM claro_resource_instance ri
            INNER JOIN  claro_user uri
            ON uri.id = ri.user_id
            INNER JOIN claro_resource res
            ON res.id = ri.resource_id
            INNER JOIN claro_resource_type rt
            ON res.resource_type_id = rt.id
            INNER JOIN claro_user ures
            ON res.user_id = ures.id
            WHERE
            ri.parent_id = {$parent->getId()}
            AND rt.is_listable = {$isListable}
            ";
            if ($resourceTypeId != 0) {
                $sql .= "AND rt.id = {$resourceTypeId}";
            }

        return $this->_em
                ->getConnection()
                ->query($sql)
                ->fetchAll(\PDO::FETCH_ASSOC);
    }
}