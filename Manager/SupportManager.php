<?php

namespace FormaLibre\SupportBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use FormaLibre\SupportBundle\Entity\Comment;
use FormaLibre\SupportBundle\Entity\Ticket;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("formalibre.manager.support_manager")
 */
class SupportManager
{
    private $om;
    private $pagerFactory;

    private $ticketRepo;

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory" = @DI\Inject("claroline.pager.pager_factory")
     * })
     */
    public function __construct(ObjectManager $om, PagerFactory $pagerFactory)
    {
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
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

    public function generateTicketNum(User $user)
    {
        $num = 1;
        $lastNum = $this->ticketRepo->findLastTicketNumByUser($user);

        if (!is_null($lastNum['ticket_num'])) {
            $num = $lastNum['ticket_num'] + 1;
        }

        return $num;
    }

    public function persistComment(Comment $comment)
    {
        $this->om->persist($comment);
        $this->om->flush();
    }

    public function deleteComment(Comment $comment)
    {
        $this->om->remove($comment);
        $this->om->flush();
    }


    /**************************************
     * Access to TicketRepository methods *
     **************************************/

    public function getAllTickets(
        $search = '',
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $withPager = true,
        $page = 1,
        $max = 50
    )
    {
        $tickets = empty($search) ?
            $this->ticketRepo->findAllTickets($orderedBy, $order) :
            $this->ticketRepo->findAllSearchedTickets($search, $orderedBy, $order);

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($tickets, $page, $max) :
            $tickets;
    }

    public function getTicketsByUser(
        User $user,
        $search = '',
        $orderedBy = 'num',
        $order = 'ASC',
        $withPager = true,
        $page = 1,
        $max = 50
    )
    {
        $tickets = empty($search) ?
            $this->ticketRepo->findTicketsByUser($user, $orderedBy, $order) :
            $this->ticketRepo->findSearchedTicketByUser($user, $search, $orderedBy, $order);

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($tickets, $page, $max) :
            $tickets;
    }
}
