<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

class ResourceRightsRepository extends EntityRepository
{
    public function getDefaultForWorkspace(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT rrw FROM Claroline\CoreBundle\Entity\Workspace\ResourceRights rrw
            JOIN rrw.role role
            JOIN role.workspace ws
            WHERE ws.id = {$workspace->getId()}
            AND rrw.resource IS NULL
            AND role.roleType = 2
            ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getCustomForResource(AbstractWorkspace $workspace, AbstractResource $resource)
    {
        $dql = "
            SELECT rrw FROM Claroline\CoreBundle\Entity\Workspace\ResourceRights rrw
            JOIN rrw.role role
            JOIN role.workspace ws
            WHERE ws.id = {$workspace->getId()}
            AND rrw.resource = {$resource->getId()}
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getAllForResource(AbstractResource $resource)
    {
       $dql = "
            SELECT rrw FROM Claroline\CoreBundle\Entity\Workspace\ResourceRights rrw
            JOIN rrw.role role
            JOIN role.workspace ws
            WHERE ws.id = {$resource->getWorkspace()->getId()}
            AND rrw.resource = {$resource->getId()}
            OR ws.id = {$resource->getWorkspace()->getId()}
            AND rrw.resource IS NULL
            ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}