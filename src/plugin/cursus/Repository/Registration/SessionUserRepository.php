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

use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Doctrine\ORM\EntityRepository;

class SessionUserRepository extends EntityRepository
{
    /**
     * Find registered users which are not yet registered to the workspace of the session.
     * NB. It will only return fully registered users (confirmed and validated).
     *
     * @return SessionUser[]
     */
    public function findNotRegisteredToWorkspace(Session $session): array
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT su
                FROM Claroline\CursusBundle\Entity\Registration\SessionUser AS su
                LEFT JOIN su.session AS s
                WHERE su.session = :session
                  AND su.confirmed = true
                  AND su.validated = true
                  AND NOT EXISTS (
                    SELECT u
                    FROM Claroline\CoreBundle\Entity\User AS u
                    LEFT JOIN u.roles AS r
                    WHERE su.user = u
                      AND r.workspace = s.workspace
                  )
            ')
            ->setParameters([
                'session' => $session,
            ])
            ->getResult();
    }
}
