<?php

namespace FormaLibre\SupportBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use FormaLibre\SupportBundle\Entity\Type;
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

    public function findAllSearchedTickets(
        $search,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    )
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            WHERE UPPER(t.title) LIKE :search
            OR UPPER(t.description) LIKE :search
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

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

    public function findSearchedTicketsByUser(
        User $user,
        $search,
        $orderedBy = 'num',
        $order = 'ASC'
    )
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            WHERE t.user = :user
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
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getSingleResult();
    }

    public function findTicketsByLevel(
        Type $type,
        $level,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    )
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            WHERE t.type = :type
            AND t.level = :level
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $type);
        $query->setParameter('level', $level);

        return $query->getResult();
    }

    public function findSearchedTicketsByLevel(
        Type $type,
        $level,
        $search,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    )
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            JOIN t.user u
            WHERE t.type = :type
            AND t.level = :level
            AND (
                UPPER(t.title) LIKE :search
                OR UPPER(t.description) LIKE :search
                OR UPPER(t.contactMail) LIKE :search
                OR UPPER(t.contactPhone) LIKE :search
                OR UPPER(u.username) LIKE :search
                OR UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR CONCAT(UPPER(u.firstName), CONCAT(\' \', UPPER(u.lastName))) LIKE :search
                OR CONCAT(UPPER(u.lastName), CONCAT(\' \', UPPER(u.firstName))) LIKE :search
            )
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $type);
        $query->setParameter('level', $level);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findTicketsByInterventionUser(
        Type $type,
        User $user,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    )
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            JOIN t.interventions i
            WHERE t.type = :type
            AND i.user = :user
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $type);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findSearchedTicketsByInterventionUser(
        Type $type,
        User $user,
        $search,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    )
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            JOIN t.interventions i
            JOIN t.user u
            WHERE t.type = :type
            AND i.user = :user
            AND (
                UPPER(t.title) LIKE :search
                OR UPPER(t.description) LIKE :search
                OR UPPER(t.contactMail) LIKE :search
                OR UPPER(t.contactPhone) LIKE :search
                OR UPPER(u.username) LIKE :search
                OR UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR CONCAT(UPPER(u.firstName), CONCAT(\' \', UPPER(u.lastName))) LIKE :search
                OR CONCAT(UPPER(u.lastName), CONCAT(\' \', UPPER(u.firstName))) LIKE :search
            )
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $type);
        $query->setParameter('user', $user);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findTicketsWithoutIntervention(
        Type $type,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    )
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            WHERE t.type = :type
            AND NOT EXISTS (
                SELECT i
                FROM FormaLibre\SupportBundle\Entity\Intervention i
                WHERE i.ticket = t
            )
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $type);

        return $query->getResult();
    }

    public function findSearchedTicketsWithoutIntervention(
        Type $type,
        $search,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    )
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            JOIN t.user u
            WHERE t.type = :type
            AND NOT EXISTS (
                SELECT i
                FROM FormaLibre\SupportBundle\Entity\Intervention i
                WHERE i.ticket = t
            )
            AND (
                UPPER(t.title) LIKE :search
                OR UPPER(t.description) LIKE :search
                OR UPPER(t.contactMail) LIKE :search
                OR UPPER(t.contactPhone) LIKE :search
                OR UPPER(u.username) LIKE :search
                OR UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR CONCAT(UPPER(u.firstName), CONCAT(\' \', UPPER(u.lastName))) LIKE :search
                OR CONCAT(UPPER(u.lastName), CONCAT(\' \', UPPER(u.firstName))) LIKE :search
            )
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $type);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findTicketsByInterventionStatus(
        Type $type,
        $status,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    )
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            JOIN t.interventions i
            JOIN i.status s
            WHERE t.type = :type
            AND s.name = :status
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $type);
        $query->setParameter('status', $status);

        return $query->getResult();
    }

    public function findSearchedTicketsByInterventionStatus(
        Type $type,
        $status,
        $search,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    )
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Ticket t
            JOIN t.interventions i
            JOIN i.status s
            JOIN t.user u
            WHERE t.type = :type
            AND s.name = :status
            AND (
                UPPER(t.title) LIKE :search
                OR UPPER(t.description) LIKE :search
                OR UPPER(t.contactMail) LIKE :search
                OR UPPER(t.contactPhone) LIKE :search
                OR UPPER(u.username) LIKE :search
                OR UPPER(u.firstName) LIKE :search
                OR UPPER(u.lastName) LIKE :search
                OR CONCAT(UPPER(u.firstName), CONCAT(\' \', UPPER(u.lastName))) LIKE :search
                OR CONCAT(UPPER(u.lastName), CONCAT(\' \', UPPER(u.firstName))) LIKE :search
            )
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $type);
        $query->setParameter('status', $status);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }
}
