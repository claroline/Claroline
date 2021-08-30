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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CursusBundle\Entity\Session;
use Doctrine\ORM\EntityRepository;

class EventGroupRepository extends EntityRepository
{
    public function findBySessionAndUser(Session $session, Group $group)
    {
        return $this->_em
            ->createQuery('
                SELECT eg 
                FROM Claroline\CursusBundle\Entity\Registration\EventGroup AS eg
                JOIN eg.event AS e
                WHERE eg.group = :group
                  AND e.session = :session
            ')
            ->setParameters([
                'session' => $session,
                'group' => $group,
            ])
            ->getResult();
    }
}
