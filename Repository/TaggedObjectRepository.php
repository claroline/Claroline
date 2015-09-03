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

use Claroline\CoreBundle\Entity\User;
use Claroline\TagBundle\Entity\Tag;
use Doctrine\ORM\EntityRepository;

class TaggedObjectRepository extends EntityRepository
{
    public function findAllTaggedObjects(
        User $user = null,
        $withPlatform = false,
        $orderedBy = 'name',
        $order = 'ASC'
    )
    {
        if (is_null($user)) {
            $dql = "
                SELECT ti
                FROM Claroline\TagBundle\Entity\TaggedObject ti
                JOIN ti.tag t
                WHERE t.user IS NULL
                ORDER BY t.{$orderedBy} {$order}
            ";
            $query = $this->_em->createQuery($dql);
        } else {

            if ($withPlatform) {
                $dql = "
                    SELECT ti
                    FROM Claroline\TagBundle\Entity\TaggedObject ti
                    JOIN ti.tag t
                    WHERE t.user IS NULL
                    OR t.user = :user
                    ORDER BY t.{$orderedBy} {$order}
                ";
            } else {
                $dql = "
                    SELECT ti
                    FROM Claroline\TagBundle\Entity\TaggedObject ti
                    JOIN ti.tag t
                    WHERE t.user = :user
                    ORDER BY t.{$orderedBy} {$order}
                ";
            }
            $query = $this->_em->createQuery($dql);
            $query->setParameter('user', $user);
        }

        return $query->getResult();
    }

    public function findSearchedTaggedObjects(
        $search,
        User $user = null,
        $withPlatform = false,
        $orderedBy = 'name',
        $order = 'ASC'
    )
    {
        if (is_null($user)) {
            $dql = "
                SELECT ti
                FROM Claroline\TagBundle\Entity\TaggedObject ti
                JOIN ti.tag t
                WHERE t.user IS NULL
                AND UPPER(t.name) LIKE :search
                ORDER BY t.{$orderedBy} {$order}
            ";
            $query = $this->_em->createQuery($dql);
        } else {

            if ($withPlatform) {
                $dql = "
                    SELECT ti
                    FROM Claroline\TagBundle\Entity\TaggedObject ti
                    JOIN ti.tag t
                    WHERE (
                        t.user IS NULL
                        OR t.user = :user
                    )
                    AND UPPER(t.name) LIKE :search
                    ORDER BY t.{$orderedBy} {$order}
                ";
            } else {
                $dql = "
                    SELECT ti
                    FROM Claroline\TagBundle\Entity\TaggedObject ti
                    JOIN ti.tag t
                    WHERE t.user = :user
                    AND UPPER(t.name) LIKE :search
                    ORDER BY t.{$orderedBy} {$order}
                ";
            }
            $query = $this->_em->createQuery($dql);
            $query->setParameter('user', $user);
        }
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findOneTaggedObjectByTagAndObject(Tag $tag, $objectId, $objectClass)
    {
        $dql = '
            SELECT ti
            FROM Claroline\TagBundle\Entity\TaggedObject ti
            WHERE ti.tag = :tag
            AND ti.objectId = :objectId
            AND ti.objectClass = :objectClass
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
            SELECT ti
            FROM Claroline\TagBundle\Entity\TaggedObject ti
            JOIN ti.tag t
            WHERE t IN (:tags)
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tags', $tags);

        return $query->getResult();
    }
}
