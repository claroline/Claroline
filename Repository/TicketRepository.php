<?php

namespace FormaLibre\SupportBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class TicketRepository extends EntityRepository
{
    public function findAllTickets($orderedBy = 'creationDate', $order = 'DESC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findTicketsByUser(User $user, $orderedBy = 'num', $order = 'ASC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            WHERE t.user = :user
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findAllNonAdminTickets($orderedBy = 'creationDate', $order = 'DESC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            WHERE NOT EXISTS (
                SELECT at
                FROM FormaLibre\SupportBundle\Entity\AdminTicket at
                WHERE at.ticket = t
            )
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findAllNonAdminTicketsByUser(
        User $user,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    )
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            WHERE t.user = :user
            AND NOT EXISTS (
                SELECT at
                FROM FormaLibre\SupportBundle\Entity\AdminTicket at
                WHERE at.ticket = t
            )
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findLastTicketNumByUser(User $user)
    {
        $dql = "
            SELECT MAX(t.num) AS ticket_num
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            WHERE t.user = :user
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getSingleResult();
    }
}
