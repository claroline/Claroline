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
use Claroline\CursusBundle\Entity\SessionEventSet;
use Doctrine\ORM\EntityRepository;

class SessionEventSetRepository extends EntityRepository
{
    /**
     * @param CourseSession $session
     * @param string        $name
     *
     * @return SessionEventSet|null
     */
    public function findSessionEventSetBySessionAndName(CourseSession $session, $name)
    {
        $dql = "
            SELECT ses
            FROM Claroline\CursusBundle\Entity\SessionEventSet ses
            WHERE ses.session = :session
            AND UPPER(ses.name) = :name
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $upperName = strtoupper($name);
        $query->setParameter('name', $upperName);

        return $query->getOneOrNullResult();
    }
}
