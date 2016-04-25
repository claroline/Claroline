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
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Doctrine\ORM\EntityRepository;

class WorkspaceTagRepository extends EntityRepository
{
    public function findNonEmptyTagsByUser(User $user)
    {
        $dql = '
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            INNER JOIN Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t WITH t = rwt.tag
            WHERE t.user = :user
            ORDER BY t.name ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findNonEmptyAdminTags()
    {
        $dql = '
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            INNER JOIN Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t WITH t = rwt.tag
            WHERE t.user IS NULL
            ORDER BY t.name ASC
        ';
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findNonEmptyAdminTagsByWorspaces(array $workspaces)
    {
        if (count($workspaces) === 0) {
            throw new \InvalidArgumentException('Array argument cannot be empty');
        }

        $dql = '
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            INNER JOIN Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t WITH t = rwt.tag
            INNER JOIN Claroline\CoreBundle\Entity\Workspace\Workspace w WITH w = rwt.workspace
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
        $dql .= '
            )
            ORDER BY t.name ASC
        ';

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findPossibleAdminChildren(WorkspaceTag $tag)
    {
        $dql = '
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user IS NULL
            AND NOT EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                WHERE h.user IS NULL
                AND (
                    (h.tag = :tag AND h.parent = t)
                    OR (h.tag = t AND h.parent = :tag AND h.level = 1)
                )
            )
            ORDER BY t.name ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tag', $tag);

        return $query->getResult();
    }

    public function findPossibleAdminChildrenByName(WorkspaceTag $tag, $search)
    {
        $dql = '
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user IS NULL
            AND NOT EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                WHERE h.user IS NULL
                AND (
                    (h.tag = :tag AND h.parent = t)
                    OR (h.tag = t AND h.parent = :tag AND h.level = 1)
                )
            )
            AND UPPER(t.name) LIKE :search
            ORDER BY t.name ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tag', $tag);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findPossibleChildren(User $user, WorkspaceTag $tag)
    {
        $dql = '
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user = :user
            AND NOT EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                WHERE h.user = :user
                AND (
                    (h.tag = :tag AND h.parent = t)
                    OR (h.tag = t AND h.parent = :tag AND h.level = 1)
                )
            )
            ORDER BY t.name ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('tag', $tag);

        return $query->getResult();
    }

    public function findPossibleChildrenByName(
        User $user,
        WorkspaceTag $tag,
        $search
    ) {
        $dql = '
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user = :user
            AND NOT EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                WHERE h.user = :user
                AND (
                    (h.tag = :tag AND h.parent = t)
                    OR (h.tag = t AND h.parent = :tag AND h.level = 1)
                )
            )
            AND UPPER(t.name) LIKE :search
            ORDER BY t.name ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('tag', $tag);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findAdminChildren(WorkspaceTag $tag)
    {
        $dql = '
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user IS NULL
            AND EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                WHERE h.user IS NULL
                AND h.tag = t
                AND h.parent = :tag
                AND h.level = 1
            )
            ORDER BY t.name ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tag', $tag);

        return $query->getResult();
    }

    public function findChildren(User $user, WorkspaceTag $tag)
    {
        $dql = '
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user = :user
            AND EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                WHERE h.user = :user
                AND h.tag = t
                AND h.parent = :tag
                AND h.level = 1
            )
            ORDER BY t.name ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('tag', $tag);

        return $query->getResult();
    }

    /**
     * Find all admin tags that don't have any parents.
     */
    public function findAdminRootTags()
    {
        $dql = '
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user IS NULL
            AND NOT EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                WHERE h.user IS NULL
                AND h.tag = t
                AND h.level > 0
            )
            ORDER BY t.name
        ';
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Find all tags that don't have any parents.
     */
    public function findRootTags(User $user)
    {
        $dql = '
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user = :user
            AND NOT EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                WHERE h.user = :user
                AND h.tag = t
                AND h.level > 0
            )
            ORDER BY t.name
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    /**
     * Find all admin tags that are children of given tag
     * Given admin tag is included.
     */
    public function findAdminChildrenFromTag(WorkspaceTag $workspaceTag)
    {
        $dql = '
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user IS NULL
            AND EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                WHERE h.user IS NULL
                AND h.tag = t
                AND h.parent = :workspaceTag
            )
            ORDER BY t.name
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceTag', $workspaceTag);

        return $query->getResult();
    }

    /**
     * Find all admin tags that are children of given tags id
     * Given admin tags are included.
     */
    public function findAdminChildrenFromTags(array $tags)
    {
        if (count($tags) === 0) {
            throw new \InvalidArgumentException('Array argument cannot be empty');
        }

        $index = 0;
        $eol = PHP_EOL;
        $tagsTest = '(';

        foreach ($tags as $tag) {
            $tagsTest .= $index > 0 ? '    OR ' : '    ';
            $tagsTest .= "p.id = {$tag}{$eol}";
            ++$index;
        }
        $tagsTest .= "){$eol}";

        $dql = "
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user IS NULL
            AND EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                JOIN h.parent p
                WHERE h.user IS NULL
                AND h.tag = t
                AND {$tagsTest}
            )
            ORDER BY t.name
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Find all tags that are children of given tag
     * Given tag is included.
     */
    public function findChildrenFromTag(User $user, WorkspaceTag $workspaceTag)
    {
        $dql = '
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user = :user
            AND EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                WHERE h.user = :user
                AND h.tag = t
                AND h.parent = :workspaceTag
            )
            ORDER BY t.name
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('workspaceTag', $workspaceTag);

        return $query->getResult();
    }

    /**
     * Find all tags that are children of given tags id
     * Given tags are included.
     */
    public function findChildrenFromTags(User $user, array $tags)
    {
        if (count($tags) === 0) {
            throw new \InvalidArgumentException('Array argument cannot be empty');
        }

        $index = 0;
        $eol = PHP_EOL;
        $tagsTest = '(';

        foreach ($tags as $tag) {
            $tagsTest .= $index > 0 ? '    OR ' : '    ';
            $tagsTest .= "p.id = {$tag}{$eol}";
            ++$index;
        }
        $tagsTest .= "){$eol}";

        $dql = "
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user = :user
            AND EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                JOIN h.parent p
                WHERE h.user = :user
                AND h.tag = t
                AND {$tagsTest}
            )
            ORDER BY t.name
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    /**
     * Find all admin tags that is parent of the given tag.
     */
    public function findAdminParentsFromTag(WorkspaceTag $tag)
    {
        $dql = '
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user IS NULL
            AND EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                WHERE h.user IS NULL
                AND h.tag = :tag
                AND h.parent = t
            )
            ORDER BY t.name
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tag', $tag);

        return $query->getResult();
    }

    /**
     * Find all admin tags that is parent of the given tag.
     */
    public function findParentsFromTag(User $user, WorkspaceTag $tag)
    {
        $dql = '
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user = :user
            AND EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                WHERE h.user = :user
                AND h.tag = :tag
                AND h.parent = t
            )
            ORDER BY t.name
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('tag', $tag);

        return $query->getResult();
    }

    public function findWorkspaceTagFromIds(array $tagIds)
    {
        if (count($tagIds) === 0) {
            throw new \InvalidArgumentException('Array argument cannot be empty');
        }

        $index = 0;
        $eol = PHP_EOL;
        $tagIdsTest = '(';

        foreach ($tagIds as $tagId) {
            $tagIdsTest .= $index > 0 ? '    OR ' : '    ';
            $tagIdsTest .= "t.id = {$tagId}{$eol}";
            ++$index;
        }
        $tagIdsTest .= "){$eol}";

        $dql = "
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE {$tagIdsTest}
            ORDER BY t.id ASC
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}
