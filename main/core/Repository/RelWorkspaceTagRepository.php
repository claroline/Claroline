<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Doctrine\ORM\EntityRepository;

class RelWorkspaceTagRepository extends EntityRepository
{
    public function findByWorkspaceAndUser(Workspace $workspace, User $user)
    {
        $dql = '
            SELECT rwt, t
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            JOIN rwt.workspace w
            JOIN rwt.tag t
            JOIN t.user u
            WHERE u.id = :userId
            AND w.id = :workspaceId
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('userId', $user->getId());
        $query->setParameter('workspaceId', $workspace->getId());

        return $query->getResult();
    }

    public function findAdminByWorkspace(Workspace $workspace)
    {
        $dql = '
            SELECT rwt, t
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            JOIN rwt.workspace w
            JOIN rwt.tag t
            WHERE t.user IS NULL
            AND w.id = :workspaceId
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId());

        return $query->getResult();
    }

    public function findAdminByTag(WorkspaceTag $tag)
    {
        $dql = '
            SELECT rwt
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            WHERE rwt.tag = :tag
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tag', $tag);

        return $query->getResult();
    }

    public function findOneByWorkspaceAndTagAndUser(
        Workspace $workspace,
        WorkspaceTag $tag,
        User $user
    ) {
        $dql = '
            SELECT rwt
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            JOIN rwt.tag t
            WHERE t.user = :user
            AND rwt.workspace = :workspace
            AND t = :tag
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('tag', $tag);

        return $query->getOneOrNullResult();
    }

    public function findOneAdminByWorkspaceAndTag(Workspace $workspace, WorkspaceTag $tag)
    {
        $dql = '
            SELECT rwt
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            JOIN rwt.workspace w
            JOIN rwt.tag t
            WHERE t.user IS NULL
            AND w.id = :workspaceId
            AND t.id = :tagId
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId());
        $query->setParameter('tagId', $tag->getId());

        return $query->getOneOrNullResult();
    }

    public function findAllByWorkspaceAndUser(Workspace $workspace, User $user)
    {
        $dql = '
            SELECT rwt, t
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            JOIN rwt.workspace w
            JOIN rwt.tag t
            WHERE w.id = :workspaceId
            AND t.user = :user
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceId', $workspace->getId());
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findByUser(User $user)
    {
        $dql = '
            SELECT t.id AS tag_id, rwt AS rel_ws_tag
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            JOIN rwt.tag t
            WHERE t.user = :user
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findByAdmin()
    {
        $dql = '
            SELECT t.id AS tag_id, rwt AS rel_ws_tag
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            JOIN rwt.tag t
            JOIN rwt.workspace w
            WHERE t.user IS NULL
            ORDER BY w.name
        ';
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findAdminRelationsByTag(WorkspaceTag $workspaceTag)
    {
        $dql = '
            SELECT rwt
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            JOIN rwt.tag t
            JOIN rwt.workspace w
            WHERE t.user IS NULL
            AND t = :tag
            ORDER BY w.name
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tag', $workspaceTag);

        return $query->getResult();
    }

    public function findAdminRelationsByTagForSelfReg(WorkspaceTag $workspaceTag)
    {
        $dql = '
            SELECT rwt
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            JOIN rwt.tag t
            JOIN rwt.workspace w
            WHERE t.user IS NULL
            AND t = :tag
            AND w.displayable = true
            AND w.selfRegistration = true
            ORDER BY rwt.id
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tag', $workspaceTag);

        return $query->getResult();
    }

    public function findByAdminAndWorkspaces(array $workspaces)
    {
        if (count($workspaces) === 0) {
            throw new \InvalidArgumentException('Array argument cannot be empty');
        }

        $dql = '
            SELECT t.id AS tag_id, rwt AS rel_ws_tag
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            JOIN rwt.tag t
            JOIN rwt.workspace w
            WHERE t.user IS NULL
            AND (
        ';

        $index = 0;
        $eol = PHP_EOL;

        foreach ($workspaces as $workspace) {
            $dql .= $index > 0 ? '    OR ' : '    ';
            $dql .= "w.id = {$workspace->getId()}{$eol}";
            ++$index;
        }
        $dql .= ')';

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}
