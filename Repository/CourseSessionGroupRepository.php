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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\ORM\EntityRepository;

class CourseSessionGroupRepository extends EntityRepository
{
    public function findOneSessionGroupBySessionAndGroup(
        CourseSession $session,
        Group $group,
        $executeQuery = true
    )
    {
        $dql = '
            SELECT csg
            FROM Claroline\CursusBundle\Entity\CourseSessionGroup csg
            WHERE csg.session = :session
            AND csg.group = :group
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('group', $group);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findSessionGroupsBySession(
        CourseSession $session,
        $executeQuery = true
    )
    {
        $dql = '
            SELECT csg
            FROM Claroline\CursusBundle\Entity\CourseSessionUser csg
            WHERE csg.session = :session
            ORDER BY csg.registrationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);

        return $executeQuery ? $query->getResult() : $query;
    }
}
