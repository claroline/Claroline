<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Repository;

use Claroline\ClacoFormBundle\Entity\Comment;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class CommentRepository extends EntityRepository
{
    public function findAvailableCommentsForUser(Entry $entry, User $user)
    {
        $dql = '
            SELECT c
            FROM Claroline\ClacoFormBundle\Entity\Comment c
            LEFT JOIN c.user u
            WHERE c.entry = :entry
            AND (
                c.status = :status
                OR u = :user
            )
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('entry', $entry);
        $query->setParameter('status', Comment::VALIDATED);
        $query->setParameter('user', $user);

        return $query->getResult();
    }
}
