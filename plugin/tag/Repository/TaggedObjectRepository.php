<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Repository;

use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TagBundle\Entity\Tag;
use Doctrine\ORM\EntityRepository;

class TaggedObjectRepository extends EntityRepository
{
    public function findAllTaggedObjects(
        User $user = null,
        $withPlatform = false,
        $class = null,
        $orderedBy = 'name',
        $order = 'ASC',
        array $ids = []
    ) {
        $classTest = is_null($class) ? '' : 'AND to.objectClass = :class';
        $idsTest = count($ids) > 0 ? 'AND to.objectId IN (:ids)' : '';

        if (is_null($user)) {
            $dql = "
                SELECT to
                FROM Claroline\TagBundle\Entity\TaggedObject to
                JOIN to.tag t
                WHERE t.user IS NULL
                $classTest
                $idsTest
                ORDER BY t.{$orderedBy} {$order}
            ";
            $query = $this->_em->createQuery($dql);
        } else {
            if ($withPlatform) {
                $dql = "
                    SELECT to
                    FROM Claroline\TagBundle\Entity\TaggedObject to
                    JOIN to.tag t
                    WHERE (
                        t.user IS NULL
                        OR t.user = :user
                    )
                    $classTest
                    $idsTest
                    ORDER BY t.{$orderedBy} {$order}
                ";
            } else {
                $dql = "
                    SELECT to
                    FROM Claroline\TagBundle\Entity\TaggedObject to
                    JOIN to.tag t
                    WHERE t.user = :user
                    $classTest
                    $idsTest
                    ORDER BY t.{$orderedBy} {$order}
                ";
            }
            $query = $this->_em->createQuery($dql);
            $query->setParameter('user', $user);
        }

        if (!is_null($class)) {
            $query->setParameter('class', $class);
        }
        if (count($ids) > 0) {
            $query->setParameter('ids', $ids);
        }

        return $query->getResult();
    }

    public function findSearchedTaggedObjects(
        $search,
        User $user = null,
        $withPlatform = false,
        $class = null,
        $orderedBy = 'name',
        $order = 'ASC',
        $strictSearch = false,
        array $ids = []
    ) {
        $classTest = is_null($class) ? '' : 'AND to.objectClass = :class';
        $searchTest = $strictSearch ? '= :search' : 'LIKE :search';
        $idsTest = count($ids) > 0 ? 'AND to.objectId IN (:ids)' : '';

        if (is_null($user)) {
            $dql = "
                SELECT to
                FROM Claroline\TagBundle\Entity\TaggedObject to
                JOIN to.tag t
                WHERE t.user IS NULL
                AND UPPER(t.name) $searchTest
                $classTest
                $idsTest
                ORDER BY t.{$orderedBy} {$order}
            ";
            $query = $this->_em->createQuery($dql);
        } else {
            if ($withPlatform) {
                $dql = "
                    SELECT to
                    FROM Claroline\TagBundle\Entity\TaggedObject to
                    JOIN to.tag t
                    WHERE (
                        t.user IS NULL
                        OR t.user = :user
                    )
                    $classTest
                    $idsTest
                    AND UPPER(t.name) $searchTest
                    ORDER BY t.{$orderedBy} {$order}
                ";
            } else {
                $dql = "
                    SELECT to
                    FROM Claroline\TagBundle\Entity\TaggedObject to
                    JOIN to.tag t
                    WHERE t.user = :user
                    $classTest
                    $idsTest
                    AND UPPER(t.name) $searchTest
                    ORDER BY t.{$orderedBy} {$order}
                ";
            }
            $query = $this->_em->createQuery($dql);
            $query->setParameter('user', $user);
        }
        $upperSearch = strtoupper($search);

        if ($strictSearch) {
            $query->setParameter('search', $upperSearch);
        } else {
            $query->setParameter('search', "%{$upperSearch}%");
        }
        if (!is_null($class)) {
            $query->setParameter('class', $class);
        }
        if (count($ids) > 0) {
            $query->setParameter('ids', $ids);
        }

        return $query->getResult();
    }

    public function findOneTaggedObjectByTagAndObject(Tag $tag, $objectId, $objectClass)
    {
        $dql = '
            SELECT to
            FROM Claroline\TagBundle\Entity\TaggedObject to
            WHERE to.tag = :tag
            AND to.objectId = :objectId
            AND to.objectClass = :objectClass
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tag', $tag);
        $query->setParameter('objectId', $objectId);
        $query->setParameter('objectClass', $objectClass);

        return $query->getOneOrNullResult();
    }

    public function findOneTaggedObjectByTagNameAndObject($tagName, $objectId, $objectClass)
    {
        $dql = '
            SELECT to
            FROM Claroline\TagBundle\Entity\TaggedObject to
            JOIN to.tag t
            WHERE t.name = :tagName
            AND to.objectId = :objectId
            AND to.objectClass = :objectClass
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tagName', $tagName);
        $query->setParameter('objectId', $objectId);
        $query->setParameter('objectClass', $objectClass);

        return $query->getResult();
    }

    public function findTaggedObjectsByTags(array $tags, $orderedBy = 'name', $order = 'ASC')
    {
        $dql = "
            SELECT to
            FROM Claroline\TagBundle\Entity\TaggedObject to
            JOIN to.tag t
            WHERE t IN (:tags)
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tags', $tags);

        return $query->getResult();
    }

    public function findTaggedObjectsByClassAndIds($class, array $ids, $orderedBy = 'id', $order = 'ASC')
    {
        $dql = "
            SELECT to
            FROM Claroline\TagBundle\Entity\TaggedObject to
            WHERE to.objectClass = :class
            AND to.objectId IN (:ids)
            ORDER BY to.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('class', $class);
        $query->setParameter('ids', $ids);

        return $query->getResult();
    }

    public function findTaggedResourcesByWorkspace(Workspace $workspace, $user = 'anon.', array $roleNames = ['ROLE_ANONYMOUS'])
    {
        $isManager = false;

        foreach ($roleNames as $roleName) {
            if ($roleName === 'ROLE_WS_MANAGER_'.$workspace->getGuid()) {
                $isManager = true;
                break;
            }
        }
        $rightsTest = $isManager ?
            '' :
            'AND rrr.name IN (:roleNames)
            AND BIT_AND(rr.mask, 1) = 1';

        $dql = "
            SELECT to
            FROM Claroline\TagBundle\Entity\TaggedObject to
            WHERE to.objectClass = :objectClass
            AND to.objectId IN (
                SELECT DISTINCT r.id
                FROM Claroline\CoreBundle\Entity\Resource\ResourceNode r
                JOIN r.creator c
                LEFT JOIN r.workspace w
                LEFT JOIN w.roles wr
                LEFT JOIN r.rights rr
                LEFT JOIN rr.role rrr
                WHERE w = :workspace
                AND r.active = :active
                AND (
                    c.id = :userId
                    OR (
                        r.published = true
                        AND (r.accessibleFrom IS NULL OR r.accessibleFrom <= :currentdate)
                        AND (r.accessibleUntil IS NULL OR r.accessibleUntil >= :currentdate)
                    )
                )
                $rightsTest
            )
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('objectClass', 'Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $query->setParameter('workspace', $workspace);
        $query->setParameter('active', true);
        $userId = ($user === 'anon.') ? -1 : $user->getId();
        $query->setParameter('userId', $userId);
        $currentDate = new \DateTime();

        if (!$isManager) {
            $query->setParameter('roleNames', $roleNames);
        }
        $query->setParameter('currentdate', $currentDate->format('Y-m-d H:i:s'));

        return $query->getResult();
    }

    public function findTaggedResourcesByRoles($user = 'anon.', array $roleNames = ['ROLE_ANONYMOUS'])
    {
        $dql = "
            SELECT to
            FROM Claroline\TagBundle\Entity\TaggedObject to
            WHERE to.objectClass = :objectClass
            AND to.objectId IN (
                SELECT DISTINCT r.id
                FROM Claroline\CoreBundle\Entity\Resource\ResourceNode r
                JOIN r.creator c
                LEFT JOIN r.workspace w
                LEFT JOIN w.roles wr
                LEFT JOIN r.rights rr
                LEFT JOIN rr.role rrr
                WHERE r.active = :active
                AND (
                    c.id = :userId
                    OR (
                        r.published = true
                        AND (r.accessibleFrom IS NULL OR r.accessibleFrom <= :currentdate)
                        AND (r.accessibleUntil IS NULL OR r.accessibleUntil >= :currentdate)
                    )
                )
                AND (
                    (CONCAT('ROLE_WS_MANAGER_', w.guid) IN (:roleNames))
                    OR (rrr.name IN (:roleNames) AND BIT_AND(rr.mask, 1) = 1)
                )
            )
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('objectClass', 'Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $query->setParameter('active', true);
        $userId = ($user === 'anon.') ? -1 : $user->getId();
        $query->setParameter('userId', $userId);
        $query->setParameter('roleNames', $roleNames);
        $currentDate = new \DateTime();
        $query->setParameter('currentdate', $currentDate->format('Y-m-d H:i:s'));

        return $query->getResult();
    }

    /********************************
     * Return casted tagged objects *
     ********************************/

    public function findObjectsByClassAndIds($class, array $ids, $orderedBy = 'id', $order = 'ASC')
    {
        $dql = "
            SELECT o
            FROM $class o
            WHERE o.id IN (:ids)
            ORDER BY o.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('ids', $ids);

        return $query->getResult();
    }

    public function findTaggedWorkspaces($tag)
    {
        $dql = "
          SELECT DISTINCT w
          FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
          WHERE w.id IN (
              SELECT to.objectId
              FROM Claroline\TagBundle\Entity\TaggedObject to
              JOIN to.tag t
              WHERE to.objectClass = :workspaceClass
              AND t.name = :tag
          )
      ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaceClass', 'Claroline\CoreBundle\Entity\Workspace\Workspace');
        $query->setParameter('tag', $tag);

        return $query->getResult();
    }

    public function findTaggedWorkspacesByRoles($tag, array $roles, $orderedBy = 'id', $order = 'ASC', $type = 0)
    {
        $dql = "
            SELECT DISTINCT w
            FROM Claroline\CoreBundle\Entity\Workspace\Workspace w
            WHERE w.id IN (
                SELECT to.objectId
                FROM Claroline\TagBundle\Entity\TaggedObject to
                JOIN to.tag t
                WHERE to.objectClass = :workspaceClass
                AND t.name = :tag
            )
            AND EXISTS (
                SELECT ot
                FROM Claroline\CoreBundle\Entity\Tool\OrderedTool ot
                JOIN ot.rights r
                JOIN r.role rr
                WHERE ot.workspace = w
                AND ot.type = :type
                AND rr IN (:roles)
                AND BIT_AND(r.mask, :open) = :open
                ORDER BY ot.order
            )
            ORDER BY w.{$orderedBy} {$order}
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);
        $query->setParameter('workspaceClass', 'Claroline\CoreBundle\Entity\Workspace\Workspace');
        $query->setParameter('tag', $tag);
        $query->setParameter('open', ToolMaskDecoder::$defaultValues['open']);
        $query->setParameter('type', $type);

        return $query->getResult();
    }
}
