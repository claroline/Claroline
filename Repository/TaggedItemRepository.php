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

class TaggedItemRepository extends EntityRepository
{
    public function findSearchedPlatformTaggedItems($search)
    {
        $dql = '
            SELECT ti
            FROM Claroline\TagBundle\Entity\TaggedItem ti
            JOIN ti.tag t
            WHERE t.user IS NULL
            AND UPPER(t.name) LIKE :search
        ';
        $query = $this->_em->createQuery($dql);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findSearchedUserTaggedItems(
        User $user,
        $search,
        $withPlatform = false
    )
    {
        if ($withPlatform) {
            $dql = '
                SELECT ti
                FROM Claroline\TagBundle\Entity\TaggedItem ti
                JOIN ti.tag t
                WHERE (
                    t.user IS NULL
                    OR t.user = :user
                )
                AND UPPER(t.name) LIKE :search
            ';
        } else {
            $dql = '
                SELECT ti
                FROM Claroline\TagBundle\Entity\TaggedItem ti
                JOIN ti.tag t
                WHERE t.user = :user
                AND UPPER(t.name) LIKE :search
            ';
        }
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findOneTaggedItemByTagAndItem(Tag $tag, $itemId, $itemClass)
    {
        $dql = '
            SELECT ti
            FROM Claroline\TagBundle\Entity\TaggedItem ti
            WHERE ti.tag = :tag
            AND ti.itemId = :itemId
            AND ti.itemClass = :itemClass
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('tag', $tag);
        $query->setParameter('itemId', $itemId);
        $query->setParameter('itemClass', $itemClass);

        return $query->getOneOrNullResult();
    }
}
