<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class SubjectRepository extends EntityRepository
{
    public function findSubjectsByParticipant(User $user)
    {
        $dql = '
            SELECT DISTINCT s
            FROM Claroline\ForumBundle\Entity\Subject s
            WHERE EXISTS (
                SELECT m
                FROM Claroline\ForumBundle\Entity\Message m
                JOIN m.subject ms
                JOIN m.creator mc
                WHERE mc = :user
                AND ms = s
            )
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }
}
