<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FormaLibre\ReservationBundle\Repository;

use Doctrine\ORM\EntityRepository;
use FormaLibre\ReservationBundle\Entity\Reservation;

class ReservationRepository extends EntityRepository
{
    public function findByDateAndResource(Reservation $reservation, $reservationIsAlreadyPersisted = false)
    {
        $sql = "SELECT r FROM FormaLibre\ReservationBundle\Entity\Reservation r
        INNER JOIN Claroline\AgendaBundle\Entity\Event e WITH r.event = e
        WHERE ((e.start > :start AND e.start < :end) OR (e.end > :start AND e.end < :end)) AND r.resource = :resource";

        if ($reservationIsAlreadyPersisted) {
            $sql .= " AND r <> :reservation";
        }

        $query = $this->_em->createQuery($sql);
        $query->setParameter('start', $reservation->getStartInTimestamp());
        $query->setParameter(':end', $reservation->getEndInTimestamp());
        $query->setParameter(':resource', $reservation->getResource());

        if ($reservationIsAlreadyPersisted) {
            $query->setParameter(':reservation', $reservation);
        }

        return $query->getResult();
    }
}
