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

use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\ORM\EntityRepository;

class SessionEventRepository extends EntityRepository
{
    public function findEventsBySession(CourseSession $session, $orderedBy = 'startDate', $order = 'ASC', $executeQuery = true)
    {
        $dql = "
            SELECT se
            FROM Claroline\CursusBundle\Entity\SessionEvent se
            WHERE se.session = :session
            ORDER BY se.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);

        return $executeQuery ? $query->getResult() : $query;
    }
}
