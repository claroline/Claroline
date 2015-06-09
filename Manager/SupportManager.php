<?php

namespace FormaLibre\SupportBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use FormaLibre\SupportBundle\Entity\AdminTicket;
use FormaLibre\SupportBundle\Entity\Ticket;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("formalibre.manager.support_manager")
 */
class SupportManager
{
    private $om;
    private $adminTicketRepo;
    private $ticketRepo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->adminTicketRepo =
            $om->getRepository('FormaLibreSupportBundle:AdminTicket');
        $this->ticketRepo =
            $om->getRepository('FormaLibreSupportBundle:Ticket');
    }

    public function persistTicket(Ticket $ticket)
    {
        $this->om->persist($ticket);
        $this->om->flush();
    }

    public function deleteTicket(Ticket $ticket)
    {
        $this->om->remove($ticket);
        $this->om->flush();
    }

    public function persistAdminTicket(AdminTicket $adminTicket)
    {
        $this->om->persist($adminTicket);
        $this->om->flush();
    }

    public function deleteAdminTicket(AdminTicket $adminTicket)
    {
        $this->om->remove($adminTicket);
        $this->om->flush();
    }

    public function generateTicketNum(User $user)
    {
        $num = 1;
        $lastNum = $this->ticketRepo->findLastTicketNumByUser($user);

        if (!is_null($lastNum['ticket_num'])) {
            $num = $lastNum['ticket_num'] + 1;
        }

        return $num;
    }


    /**************************************
     * Access to TicketRepository methods *
     **************************************/

    public function getAllTickets($orderedBy = 'creationDate', $order = 'DESC')
    {
        return $this->ticketRepo->findAllTickets($orderedBy, $order);
    }

    public function getTicketsByUser(User $user, $orderedBy = 'num', $order = 'ASC')
    {
        return $this->ticketRepo->findTicketsByUser($user, $orderedBy, $order);
    }

    public function getAllNonAdminTickets($orderedBy = 'creationDate', $order = 'DESC')
    {
        return $this->ticketRepo->findAllNonAdminTickets($orderedBy, $order);
    }

    public function getAllNonAdminTicketsByUser(
        User $user,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    )
    {
        return $this->ticketRepo->findAllNonAdminTicketsByUser($user, $orderedBy, $order);
    }


    /*******************************************
     * Access to AdminTicketRepository methods *
     *******************************************/

    public function getAllAdminTickets($orderedBy = 'statusDate', $order = 'DESC')
    {
        return $this->adminTicketRepo->findAllAdminTickets($orderedBy, $order);
    }

    public function getAdminTicketsByUser(User $user, $orderedBy = 'id', $order = 'ASC')
    {
        return $this->adminTicketRepo->findAdminTicketsByUser($user, $orderedBy, $order);
    }

    public function getAdminTicketByTicket(Ticket $ticket)
    {
        return $this->adminTicketRepo->findAdminTicketByTicket($ticket);
    }
}
