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

class WorkspaceTagHierarchyRepository extends EntityRepository
{
    /**
     * Returns all admin relations where given workspaceTag is parent.
     */
    public function findAdminHierarchiesByParent(WorkspaceTag $workspaceTag)
    {
        $dql = '
            SELECT h
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
            WHERE h.user IS NULL
            AND h.parent = :workspaceTag
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceTag', $workspaceTag);

        return $query->getResult();
    }

    /**
     * Returns all relations where given workspaceTag is parent.
     */
    public function findHierarchiesByParent(
        User $user,
        WorkspaceTag $workspaceTag
    ) {
        $dql = '
            SELECT h
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
            WHERE h.user = :user
            AND h.parent = :workspaceTag
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('workspaceTag', $workspaceTag);

        return $query->getResult();
    }

    /**
     * Returns all relations where parentId is in the param array.
     */
    public function findAdminHierarchiesByParents(array $parents)
    {
        if (count($parents) === 0) {
            throw new \InvalidArgumentException('Array argument cannot be empty');
        }

        $index = 0;
        $eol = PHP_EOL;
        $parentsTest = '(';

        foreach ($parents as $parent) {
            $parentsTest .= $index > 0 ? '    OR ' : '    ';
            $parentsTest .= "p.id = {$parent}{$eol}";
            ++$index;
        }
        $parentsTest .= "){$eol}";

        $dql = "
            SELECT h
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
            JOIN h.parent p
            WHERE h.user IS NULL
            AND {$parentsTest}
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns all relations where parentId is in the param array.
     */
    public function findHierarchiesByParents(User $user, array $parents)
    {
        if (count($parents) === 0) {
            throw new \InvalidArgumentException('Array argument cannot be empty');
        }

        $index = 0;
        $eol = PHP_EOL;
        $parentsTest = '(';

        foreach ($parents as $parent) {
            $parentsTest .= $index > 0 ? '    OR ' : '    ';
            $parentsTest .= "p.id = {$parent}{$eol}";
            ++$index;
        }
        $parentsTest .= "){$eol}";

        $dql = "
            SELECT h
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
            JOIN h.parent p
            WHERE h.user = :user
            AND {$parentsTest}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    /**
     * Find all admin hierarchies (with level > 0) where
     * parents and children ids are in the given arrays.
     */
    public function findAdminHierarchiesByParentsAndChildren(array $parents, array $children)
    {
        if (count($parents) === 0 || count($children) === 0) {
            throw new \InvalidArgumentException('Array argument cannot be empty');
        }

        $parentIndex = 0;
        $eol = PHP_EOL;
        $parentsTest = '(';

        foreach ($parents as $parent) {
            $parentsTest .= $parentIndex > 0 ? '    OR ' : '    ';
            $parentsTest .= "p.id = {$parent}{$eol}";
            ++$parentIndex;
        }
        $parentsTest .= "){$eol}";

        $childrenIndex = 0;
        $childrenTest = '(';

        foreach ($children as $child) {
            $childrenTest .= $childrenIndex > 0 ? '    OR ' : '    ';
            $childrenTest .= "t.id = {$child}{$eol}";
            ++$childrenIndex;
        }
        $childrenTest .= "){$eol}";

        $dql = "
            SELECT h
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
            JOIN h.parent p
            JOIN h.tag t
            WHERE h.user IS NULL
            AND h.level > 0
            AND {$parentsTest}
            AND {$childrenTest}
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Find all hierarchies (with level > 0) where
     * parents and children ids are in the given arrays.
     */
    public function findHierarchiesByParentsAndChildren(User $user, array $parents, array $children)
    {
        if (count($parents) === 0 || count($children) === 0) {
            throw new \InvalidArgumentException('Array argument cannot be empty');
        }

        $parentIndex = 0;
        $eol = PHP_EOL;
        $parentsTest = '(';

        foreach ($parents as $parent) {
            $parentsTest .= $parentIndex > 0 ? '    OR ' : '    ';
            $parentsTest .= "p.id = {$parent}{$eol}";
            ++$parentIndex;
        }
        $parentsTest .= "){$eol}";

        $childrenIndex = 0;
        $childrenTest = '(';

        foreach ($children as $child) {
            $childrenTest .= $childrenIndex > 0 ? '    OR ' : '    ';
            $childrenTest .= "t.id = {$child}{$eol}";
            ++$childrenIndex;
        }
        $childrenTest .= "){$eol}";

        $dql = "
            SELECT h
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
            JOIN h.parent p
            JOIN h.tag t
            WHERE h.user = :user
            AND h.level > 0
            AND {$parentsTest}
            AND {$childrenTest}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    /**
     * Find all hierarchies created by a given user.
     * Ordered by child Tag name.
     */
    public function findAllByUser(User $user)
    {
        $dql = '
            SELECT h
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
            JOIN h.tag t
            WHERE h.user = :user
            ORDER BY t.name ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    /**
     * Find all admin hierarchies
     * Ordered by child Tag name.
     */
    public function findAllAdmin()
    {
        $dql = '
            SELECT h
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
            JOIN h.tag t
            WHERE h.user IS NULL
            ORDER BY t.name ASC
        ';
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}
