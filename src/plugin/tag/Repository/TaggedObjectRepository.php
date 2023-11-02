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
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('tag', $tag);
        $query->setParameter('objectId', $objectId);
        $query->setParameter('objectClass', $objectClass);

        return $query->getOneOrNullResult();
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
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('class', $class);
        $query->setParameter('ids', $ids);

        return $query->getResult();
    }
}
