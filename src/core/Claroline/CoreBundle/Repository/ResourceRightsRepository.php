<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;

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

    /**
     * Used by the ResourceVoter.
     *
     * @param type User $user
     * @param type AbstractResource $resource
     *
     * @return ResourceRights;
     */
    public function getRights(User $user, AbstractResource $resource)
    {

        $dql = "
            SELECT rrw FROM Claroline\CoreBundle\Entity\Workspace\ResourceRights rrw
            JOIN rrw.role role WITH role IN
                (SELECT userrole FROM Claroline\CoreBundle\Entity\Role userrole
                JOIN userrole.workspace ws
                JOIN ws.resources res
                JOIN userrole.users u
                WHERE res.id = {$resource->getId()}
                AND u.id = {$user->getId()}

            )
            ORDER BY rrw.id";

       $query = $this->_em->createQuery($dql);

       return $query->getResult();
    }
}