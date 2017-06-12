<?php

namespace FormaLibre\SupportBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class TicketRepository extends EntityRepository
{
    public function findOngoingTickets($orderedBy = 'creationDate', $order = 'DESC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            LEFT JOIN t.status s
            WHERE (
              t.status IS NULL
              OR s.code != 'FA'
            )
            AND t.adminActive = true
            AND t.forwarded = false
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findSearchedOngoingTickets($search, $orderedBy = 'creationDate', $order = 'DESC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            LEFT JOIN t.status s
            WHERE (
                t.status IS NULL
                OR s.code != 'FA'
            )
            AND t.adminActive = true
            AND t.forwarded = false
            AND (
                UPPER(t.title) LIKE :search
                OR UPPER(t.description) LIKE :search
            )
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findMyTickets(User $user, $orderedBy = 'creationDate', $order = 'DESC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            LEFT JOIN t.status s
            WHERE (
                t.status IS NULL
                OR s.code != 'FA'
            )
            AND t.adminActive = true
            AND t.forwarded = false
            AND EXISTS (
                SELECT tu
                FROM FormaLibre\SupportBundle\Entity\TicketUser tu
                WHERE tu.ticket = t
                AND tu.user = :user
            )
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findSearchedMyTickets(User $user, $search, $orderedBy = 'creationDate', $order = 'DESC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            LEFT JOIN t.status s
            WHERE (
                t.status IS NULL
                OR s.code != 'FA'
            )
            AND t.adminActive = true
            AND t.forwarded = false
            AND EXISTS (
                SELECT tu
                FROM FormaLibre\SupportBundle\Entity\TicketUser tu
                WHERE tu.ticket = t
                AND tu.user = :user
            )
            AND (
                UPPER(t.title) LIKE :search
                OR UPPER(t.description) LIKE :search
            )
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findClosedTickets($orderedBy = 'creationDate', $order = 'DESC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            JOIN t.status s
            WHERE s.code = 'FA'
            AND t.adminActive = true
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findSearchedClosedTickets($search, $orderedBy = 'creationDate', $order = 'DESC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            JOIN t.status s
            WHERE s.code != 'FA'
            AND t.adminActive = true
            AND (
                UPPER(t.title) LIKE :search
                OR UPPER(t.description) LIKE :search
            )
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findOngoingTicketsByUser(User $user, $orderedBy = 'creationDate', $order = 'DESC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            LEFT JOIN t.status s
            WHERE t.user = :user
            AND (
              t.status IS NULL
              OR s.code != 'FA'
            )
            AND t.userActive = true
            AND t.forwarded = false
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findSearchedOngoingTicketsByUser(User $user, $search, $orderedBy = 'creationDate', $order = 'DESC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            LEFT JOIN t.status s
            WHERE t.user = :user
            AND (
                t.status IS NULL
                OR s.code != 'FA'
            )
            AND t.userActive = true
            AND t.forwarded = false
            AND (
                UPPER(t.title) LIKE :search
                OR UPPER(t.description) LIKE :search
            )
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findClosedTicketsByUser(User $user, $orderedBy = 'creationDate', $order = 'DESC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            JOIN t.status s
            WHERE t.user = :user
            AND s.code = 'FA'
            AND t.userActive = true
            AND t.forwarded = false
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findSearchedClosedTicketsByUser(User $user, $search, $orderedBy = 'creationDate', $order = 'DESC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            JOIN t.status s
            WHERE t.user = :user
            AND s.code != 'FA'
            AND t.userActive = true
            AND t.forwarded = false
            AND (
                UPPER(t.title) LIKE :search
                OR UPPER(t.description) LIKE :search
            )
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findLastTicketNumByUser(User $user)
    {
        $dql = "
            SELECT MAX(t.num) AS ticket_num
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            WHERE t.user = :user
            AND t.forwarded = false
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getSingleResult();
    }
    public function findOngoingForwardedTickets($orderedBy = 'creationDate', $order = 'DESC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            LEFT JOIN t.status s
            WHERE (
              t.status IS NULL
              OR s.code != 'FA'
            )
            AND t.adminActive = true
            AND t.forwarded = true
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findSearchedOngoingForwardedTickets($search, $orderedBy = 'creationDate', $order = 'DESC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            LEFT JOIN t.status s
            WHERE (
                t.status IS NULL
                OR s.code != 'FA'
            )
            AND t.adminActive = true
            AND t.forwarded = true
            AND (
                UPPER(t.title) LIKE :search
                OR UPPER(t.description) LIKE :search
            )
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }
}
