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
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Session;
use Doctrine\ORM\EntityRepository;

class SessionRepository extends EntityRepository
{
    public function findByWorkspace(Workspace $workspace)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT s FROM Claroline\CursusBundle\Entity\Session AS s
                WHERE s.workspace = :workspace
            ')
            ->setParameters([
                'workspace' => $workspace,
            ])
            ->getResult();
    }

    /**
     * Finds all the Sessions which are not ended for a given Course.
     */
    public function findAvailable(Course $course)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT s 
                FROM Claroline\CursusBundle\Entity\Session AS s
                WHERE s.course = :course
                  AND (s.endDate IS NULL OR s.endDate >= :endDate)
                  AND s.hidden = false
            ')
            ->setParameters([
                'course' => $course,
                'endDate' => new \DateTime(),
            ])
            ->getResult();
    }

    public function countParticipants(Session $session): array
    {
        return [
            'tutors' => $this->countTutors($session),
            'learners' => $this->countLearners($session),
            'pending' => $this->countPending($session),
        ];
    }

    public function countTutors(Session $session): int
    {
        return $this->countUsers($session, AbstractRegistration::TUTOR);
    }

    public function countLearners(Session $session): int
    {
        $count = $this->countUsers($session, AbstractRegistration::LEARNER);

        // add groups count
        $sessionGroups = $this->getEntityManager()
            ->createQuery('
                SELECT sg 
                FROM Claroline\CursusBundle\Entity\Registration\SessionGroup AS sg
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

    public function countPending(Session $session): int
    {
        return (int) $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(DISTINCT su) 
                FROM Claroline\CursusBundle\Entity\Registration\SessionUser AS su
                LEFT JOIN su.user AS u
                WHERE su.type = :registrationType
                  AND su.session = :session
                  AND (su.confirmed = 0 OR su.validated = 0)
                  AND u.isEnabled = true AND u.isRemoved = false AND u.technical = false
            ')
            ->setParameters([
                'registrationType' => AbstractRegistration::LEARNER,
                'session' => $session,
            ])
            ->getSingleScalarResult();
    }

    private function countUsers(Session $session, string $type): int
    {
        return (int) $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(DISTINCT su) 
                FROM Claroline\CursusBundle\Entity\Registration\SessionUser AS su
                LEFT JOIN su.user AS u
                WHERE su.type = :registrationType
                  AND su.session = :session
                  AND (su.confirmed = 1 AND su.validated = 1)
                  AND u.isEnabled = true AND u.isRemoved = false
            ')
            ->setParameters([
                'registrationType' => $type,
                'session' => $session,
            ])
            ->getSingleScalarResult();
    }
}
