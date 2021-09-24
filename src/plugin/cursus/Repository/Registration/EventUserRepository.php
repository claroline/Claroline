<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Repository\Registration;

use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\Session;
use Doctrine\ORM\EntityRepository;

class EventUserRepository extends EntityRepository
{
    public function findBySessionAndUser(Session $session, User $user)
    {
        return $this->_em
            ->createQuery('
                SELECT eu 
                FROM Claroline\CursusBundle\Entity\Registration\EventUser AS eu
                JOIN eu.event AS e
                WHERE eu.user = :user
                  AND e.session = :session
            ')
            ->setParameters([
                'session' => $session,
                'user' => $user,
            ])
            ->getResult();
    }
}
