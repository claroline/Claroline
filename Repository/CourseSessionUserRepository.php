<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\ORM\EntityRepository;

class CourseSessionUserRepository extends EntityRepository
{
    public function findOneSessionUserBySessionAndUser(
        CourseSession $session,
        User $user,
        $executeQuery = true
    )
    {
        $dql = '
            SELECT csu
            FROM Claroline\CursusBundle\Entity\CourseSessionUser csu
            WHERE csu.session = :session
            AND csu.user = :user
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }
}
