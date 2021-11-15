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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Session;
use Doctrine\ORM\EntityRepository;

class SessionRepository extends EntityRepository
{
    public function findByWorkspace(Workspace $workspace)
    {
        return $this->_em
            ->createQuery('
                SELECT s FROM Claroline\CursusBundle\Entity\Session AS s
                WHERE s.workspace = :workspace
            ')
            ->setParameters([
                'workspace' => $workspace,
            ])
            ->getResult();
    }

    public function countParticipants(Session $session)
    {
        return [
            'tutors' => $this->countTutors($session),
            'learners' => $this->countLearners($session),
            'pending' => $this->countPending($session),
            'cancellations' => $this->countCancellation($session),
        ];
    }

    public function countTutors(Session $session)
    {
        return $this->countUsers($session, AbstractRegistration::TUTOR);
    }

    public function countLearners(Session $session)
    {
        $count = $this->countUsers($session, AbstractRegistration::LEARNER);

        // add groups count
        $sessionGroups = $this->_em
            ->createQuery('
                SELECT sg FROM Claroline\CursusBundle\Entity\Registration\SessionGroup AS sg
                WHERE sg.type = :registrationType
                  AND sg.session = :session
            ')
            ->setParameters([
                'registrationType' => AbstractRegistration::LEARNER,
                'session' => $session,
            ])
            ->getResult();

        foreach ($sessionGroups as $sessionGroup) {
            $count += $sessionGroup->getGroup()->getUsers()->count();
        }

        return $count;
    }

    public function countPending(Session $session)
    {
        return (int) $this->_em
            ->createQuery('
                SELECT COUNT(su) FROM Claroline\CursusBundle\Entity\Registration\SessionUser AS su
                WHERE su.type = :registrationType
                  AND su.session = :session
                  AND (su.confirmed = 0 OR su.validated = 0)
            ')
            ->setParameters([
                'registrationType' => AbstractRegistration::LEARNER,
                'session' => $session,
            ])
            ->getSingleScalarResult();
    }

    public function countCancellation(Session $session)
    {
        return (int) $this->_em
            ->createQuery('
                SELECT COUNT(sc) FROM Claroline\CursusBundle\Entity\Registration\SessionCancellation AS sc
            ')
            ->getSingleScalarResult();
    }

    private function countUsers(Session $session, string $type)
    {
        return (int) $this->_em
            ->createQuery('
                SELECT COUNT(su) FROM Claroline\CursusBundle\Entity\Registration\SessionUser AS su
                WHERE su.type = :registrationType
                  AND su.session = :session
                  AND (su.confirmed = 1 AND su.validated = 1)
            ')
            ->setParameters([
                'registrationType' => $type,
                'session' => $session,
            ])
            ->getSingleScalarResult();
    }
}
