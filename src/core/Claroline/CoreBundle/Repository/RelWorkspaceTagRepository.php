<?php
namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Doctrine\ORM\EntityRepository;

class RelWorkspaceTagRepository extends EntityRepository
{
    public function findByUserAndWorkspace(User $user, AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT rwt, t
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            JOIN rwt.workspace w
            JOIN rwt.tag t
            JOIN t.user u
            WHERE u.id = :userId
            AND w.id = :workspaceId
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $query->setParameter('workspaceId', $workspace->getId());

        return $query->getResult();
    }

    public function findOneByUserAndWorkspaceAndTag(User $user, AbstractWorkspace $workspace, WorkspaceTag $tag)
    {
        $dql = "
            SELECT rwt
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            JOIN rwt.workspace w
            JOIN rwt.tag t
            JOIN t.user u
            WHERE u.id = :userId
            AND w.id = :workspaceId
            AND t.id = :tagId
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $query->setParameter('workspaceId', $workspace->getId());
        $query->setParameter('tagId', $tag->getId());

        return $query->getOneOrNullResult();
    }
}