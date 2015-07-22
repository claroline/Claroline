<?php

namespace FormaLibre\SupportBundle\Repository;

use Doctrine\ORM\EntityRepository;
use FormaLibre\SupportBundle\Entity\Ticket;

class InterventionRepository extends EntityRepository
{
    public function findUnfinishedInterventionByTicket(
        Ticket $ticket,
        $orderedBy = 'startDate',
        $order = 'ASC'
    )
    {
        $dql = "
            SELECT i
            FROM FormaLibre\SupportBundle\Entity\Intervention i
            WHERE i.ticket = :ticket
            AND i.endDate IS NULL
            ORDER BY i.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('ticket', $ticket);

        return $query->getResult();

    }
}
