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
        $order = 'ASC'
    )
    {
        $classTest = is_null($class) ? '' : 'AND to.objectClass = :class';

        if (is_null($user)) {
            $dql = "
                SELECT to
                FROM Claroline\TagBundle\Entity\TaggedObject to
                JOIN to.tag t
                WHERE t.user IS NULL
                $classTest
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
                    ORDER BY t.{$orderedBy} {$order}
                ";
            } else {
                $dql = "
                    SELECT to
                    FROM Claroline\TagBundle\Entity\TaggedObject to
                    JOIN to.tag t
                    WHERE t.user = :user
                    $classTest
                    ORDER BY t.{$orderedBy} {$order}
                ";
            }
            $query = $this->_em->createQuery($dql);
            $query->setParameter('user', $user);
        }
        
        if (!is_null($class)) {
            $query->setParameter('class', $class);
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
        $strictSearch = false
    )
    {
        $classTest = is_null($class) ? '' : 'AND to.objectClass = :class';
        $searchTest = $strictSearch ? '= :search' : 'LIKE :search';

        if (is_null($user)) {
            $dql = "
                SELECT to
                FROM Claroline\TagBundle\Entity\TaggedObject to
                JOIN to.tag t
                WHERE t.user IS NULL
                AND UPPER(t.name) $searchTest
                $classTest
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

    public function findTaggedObjectsByClassAndIds(
        $class,
        array $ids,
        $orderedBy = 'id',
        $order = 'ASC'
    )
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

    public function findTaggedResourcesByWorkspace(Workspace $workspace)
    {
        $dql = '
            SELECT to
            FROM Claroline\TagBundle\Entity\TaggedObject to
            WHERE to.objectClass = :objectClass
            AND to.objectId IN (
                SELECT r.id
                FROM Claroline\CoreBundle\Entity\Resource\ResourceNode r
                WHERE r.workspace = :workspace
            )
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('objectClass', 'Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $query->setParameter('workspace', $workspace);

        return $query->getResult();
    }


    /********************************
     * Return casted tagged objects *
     ********************************/

    public function findObjectsByClassAndIds(
        $class,
        array $ids,
        $orderedBy = 'id',
        $order = 'ASC'
    )
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

    public function findTaggedWorkspacesByRoles(
        $tag,
        array $roles,
        $orderedBy = 'id',
        $order = 'ASC',
        $type = 0
    )
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
