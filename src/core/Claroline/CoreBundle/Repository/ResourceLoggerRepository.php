<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ResourceLoggerRepository extends EntityRepository
{
    public function getLastLogs($user, $workspace = null)
    {
        $dql = "
            SELECT rl FROM Claroline\CoreBundle\Entity\Logger\ResourceLogger rl
            JOIN rl.workspace ws
            JOIN ws.roles r
            WHERE rl.workspace IN (
                SELECT w FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w
                JOIN w.roles wr
                JOIN wr.users ur
                WHERE ur.id = {$user->getId()}
            )";

            if ($workspace !== null){
                $dql.= " AND ws.id = {$workspace->getId()}";
            }
            
            $dql.= "ORDER by rl.dateLog DESC
           ";

        $query = $this->_em->createQuery($dql);
        $query->setMaxResults(10);

        $paginator = new Paginator($query, true);

        return $paginator;
    }
}