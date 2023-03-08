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

use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Doctrine\ORM\EntityRepository;

class EventRepository extends EntityRepository
{
    public function countParticipants(Event $event)
    {
        return [
            'tutors' => $this->countTutors($event),
            'learners' => $this->countLearners($event),
        ];
    }

    public function countTutors(Event $event)
    {
        return $this->countUsers($event, AbstractRegistration::TUTOR);
    }

    public function countLearners(Event $event)
    {
        $count = $this->countUsers($event, AbstractRegistration::LEARNER);

        // add groups count
        $eventGroups = $this->getEntityManager()
            ->createQuery('
                SELECT sg FROM Claroline\CursusBundle\Entity\Registration\EventGroup AS sg
                WHERE sg.type = :registrationType
                  AND sg.event = :event
            ')
            ->setParameters([
                'registrationType' => AbstractRegistration::LEARNER,
                'event' => $event,
            ])
            ->getResult();

        foreach ($eventGroups as $eventGroup) {
            $count += $eventGroup->getGroup()->getUsers()->count();
        }

        return $count;
    }

    private function countUsers(Event $event, string $type)
    {
        return (int) $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(su) FROM Claroline\CursusBundle\Entity\Registration\EventUser AS su
                WHERE su.type = :registrationType
                  AND su.event = :event
                  AND (su.confirmed = 1 AND su.validated = 1)
            ')
            ->setParameters([
                'registrationType' => $type,
                'event' => $event,
            ])
            ->getSingleScalarResult();
    }
}
