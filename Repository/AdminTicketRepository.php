<?php

namespace FormaLibre\SupportBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use FormaLibre\SupportBundle\Entity\Ticket;

class AdminTicketRepository extends EntityRepository
{
    public function findAllAdminTickets($orderedBy = 'statusDate', $order = 'DESC')
    {
        $dql = "
            SELECT at
            FROM FormaLibre\SupportBundle\Entity\AdminTicket at
            JOIN at.ticket t
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findAdminTicketsByUser(User $user, $orderedBy = 'id', $order = 'ASC')
    {
        $dql = "
            SELECT at
            FROM FormaLibre\SupportBundle\Entity\AdminTicket at
            WHERE at.user = :user
            ORDER BY at.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findAdminTicketByTicket(Ticket $ticket)
    {
        $dql = '
            SELECT at
            FROM FormaLibre\SupportBundle\Entity\AdminTicket at
            WHERE at.ticket = :ticket
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('ticket', $ticket);

        return $query->getOneOrNullResult();
    }
}
