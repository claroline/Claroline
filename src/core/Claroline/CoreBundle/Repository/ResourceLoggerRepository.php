<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ResourceLoggerRepository extends EntityRepository
{
    public function getLastLogs($user)
    {
        $dql = "
            SELECT rl FROM Claroline\CoreBundle\Entity\Logger\ResourceLogger rl
            JOIN rl.workspace ws
            JOIN ws.roles r
            JOIN r.users u
            WHERE rl.workspace IN (
                SELECT w FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
                JOIN w.roles wr
                JOIN wr.users ur
                WHERE ur.id = {$user->getId()}
            )
            ORDER by rl.dateLog DESC
           ";

        $query = $this->_em->createQuery($dql);
        $query->setMaxResults(10);

        return $query->getResult();

    }
}