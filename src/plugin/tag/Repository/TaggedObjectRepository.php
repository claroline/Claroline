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
use Claroline\TagBundle\Entity\Tag;
use Doctrine\ORM\EntityRepository;

class TaggedObjectRepository extends EntityRepository
{
    public function countByTag(Tag $tag)
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t)')
            ->where('t.tag = :tag')
            ->setParameters([
                'tag' => $tag,
            ])
            ->getQuery()
            ->getSingleScalarResult();
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

    /********************************
     * Return casted tagged objects *
     ********************************/

    public function findObjectsByClassAndIds($class, array $ids, $orderedBy = 'id', $order = 'ASC')
    {
        $property = is_string($ids[0]) ? 'uuid' : 'id';
        $dql = "
            SELECT o
            FROM $class o
            WHERE o.{$property} IN (:ids)
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
          WHERE w.uuid IN (
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
            WHERE w.uuid IN (
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
                AND rr.type = :type
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
