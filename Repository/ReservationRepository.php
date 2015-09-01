<?php

namespace FormaLibre\ReservationBundle\Repository;

use Doctrine\ORM\EntityRepository;
use FormaLibre\ReservationBundle\Entity\Reservation;
use FormaLibre\ReservationBundle\Entity\Resource;

class ReservationRepository extends EntityRepository
{
    public function findByDateAndResource($start, $end, Resource $resource)
    {
        $sql = "SELECT r FROM FormaLibre\ReservationBundle\Entity\Reservation r
        INNER JOIN Claroline\AgendaBundle\Entity\Event e WITH r.event = e
        WHERE ((e.start BETWEEN :start AND :end) OR (e.end BETWEEN :start AND :end) OR (e.start <= :start AND e.end >= :end))
        AND e.start NOT IN (:start, :end)
        AND e.end NOT IN (:start, :end)
        AND r.resource = :resource";

        $query = $this->_em->createQuery($sql);
        $query->setParameter('start', $start + 1);
        $query->setParameter('end', $end - 1);
        $query->setParameter('resource', $resource->getId());

        return $query->getResult();
    }

    public function findByReservationDateAndResource(Reservation $reservation, $start, $end, Resource $resource)
    {
        $sql = "SELECT r FROM FormaLibre\ReservationBundle\Entity\Reservation r
        INNER JOIN Claroline\AgendaBundle\Entity\Event e WITH r.event = e
        WHERE ((e.start BETWEEN :start AND :end) OR (e.end BETWEEN :start AND :end) OR (e.start <= :start AND e.end >= :end))
        AND e.start NOT IN (:start, :end)
        AND e.end NOT IN (:start, :end)
        AND r.resource = :resource AND r <> :reservation";

        $query = $this->_em->createQuery($sql);
        $query->setParameter('reservation', $reservation->getId());
        $query->setParameter('start', $start + 1);
        $query->setParameter('end', $end - 1);
        $query->setParameter('resource', $resource->getId());

        return $query->getResult();
    }
}
