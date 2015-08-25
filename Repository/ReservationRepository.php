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
    public function findByDateAndResource(Reservation $reservation)
    {
        $sql = "SELECT r FROM FormaLibre\ReservationBundle\Entity\Reservation r
        INNER JOIN Claroline\AgendaBundle\Entity\Event e WITH r.event = e
        WHERE ((e.start BETWEEN :start AND :end) OR (e.end BETWEEN :start AND :end)) AND r.resource = :resource";
        $query = $this->_em->createQuery($sql);
        $query->setParameter('start', $reservation->getStart());
        $query->setParameter(':end', $reservation->getEnd());
        $query->setParameter(':resource', $reservation->getResource());

        return $query->getResult();
    }
}
