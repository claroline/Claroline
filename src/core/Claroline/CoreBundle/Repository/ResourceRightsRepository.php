<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;

class ResourceRightsRepository extends EntityRepository
{
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
            SELECT DISTINCT rrw FROM Claroline\CoreBundle\Entity\Workspace\ResourceRights rrw
            JOIN rrw.role role WITH role IN
                (SELECT userrole FROM Claroline\CoreBundle\Entity\Role userrole
                JOIN userrole.workspace ws
                JOIN ws.resources res
                JOIN userrole.users u
                WHERE res.id = {$resource->getId()}
                AND u.id = {$user->getId()}

            )
            JOIN rrw.resource resource
            WHERE resource.id = {$resource->getId()}
            ORDER BY rrw.id";

       $query = $this->_em->createQuery($dql);

       return $query->getOneOrNullResult();
    }
}