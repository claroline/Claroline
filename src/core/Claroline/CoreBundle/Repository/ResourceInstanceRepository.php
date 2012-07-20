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

    public function getListableChildren(ResourceInstance $resourceInstance)
    {
        $dql = "
            SELECT ri FROM Claroline\CoreBundle\Entity\Resource\ResourceInstance ri
            JOIN ri.parent par
            JOIN ri.abstractResource res
            WHERE par.id = {$resourceInstance->getId()}
            AND res.resourceType IN
            (
                SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
                WHERE rt.isListable = 1
            )
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getResourceList(ResourceType $resourceType, User $user)
    {
        $dql = "
            SELECT DISTINCT res FROM Claroline\CoreBundle\Entity\Resource\AbstractResource res
            WHERE res.id IN
            (
                SELECT ri.id FROM Claroline\CoreBundle\Entity\Resource\ResourceInstance ri
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
            )
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}