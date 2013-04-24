<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class WorkspaceTagRepository extends EntityRepository
{
    public function findNonEmptyTagsByUser(User $user)
    {
        $dql = "
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            INNER JOIN Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t WITH t = rwt.tag
            WHERE t.user = :user
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findNonEmptyAdminTags()
    {
        $dql = "
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            INNER JOIN Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t WITH t = rwt.tag
            WHERE t.user IS NULL
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }


    public function findNonEmptyAdminTagsByWorspaces(array $workspaces)
    {
        if (count($workspaces) === 0) {
            throw new \InvalidArgumentException("Array argument cannot be empty");
        }

        $dql = "
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            INNER JOIN Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t WITH t = rwt.tag
            INNER JOIN Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w WITH w = rwt.workspace
            WHERE t.user IS NULL
            AND (
        ";

        $index = 0;
        $eol = PHP_EOL;

        foreach ($workspaces as $workspace) {
            $dql .= $index > 0 ? '    OR ' : '    ';
            $dql .= "w.id = {$workspace->getId()}{$eol}";
            $index++;
        }
        $dql .= ")";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}