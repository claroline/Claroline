<?php

namespace FormaLibre\SupportBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use FormaLibre\SupportBundle\Entity\Status;
use FormaLibre\SupportBundle\Entity\Ticket;
use FormaLibre\SupportBundle\Entity\Type;
use FormaLibre\SupportBundle\Manager\SupportManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('formalibre_support_management_tool')")
 */
class AdminSupportController extends Controller
{
    private $supportManager;

    /**
     * @DI\InjectParams({
     *     "supportManager" = @DI\Inject("formalibre.manager.support_manager")
     * })
     */
    public function __construct(SupportManager $supportManager)
    {
        $this->supportManager = $supportManager;
    }

    /**
     * @EXT\Route(
     *     "/admin/support/index",
     *     name="formalibre_admin_support_index",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportIndexAction()
    {
        $types = $this->supportManager->getAllTypes();

        return array('types' => $types);
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/{type}/tabs/active/{supportName}",
     *     name="formalibre_admin_support_type_tabs",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportTabsAction(
        User $authenticatedUser,
        Type $type,
        $supportName
    )
    {
        $newTickets = $this->supportManager->getTicketsWithoutIntervention(
            $type,
            '',
            'creationDate',
            'DESC',
            true,
            1,
            50
        );
        $nbNewTickets = count($newTickets);
        $l1Tickets = $this->supportManager->getTicketsByLevel($type, 1, '', 'id', 'ASC', false);
        $nbL1Tickets = count($l1Tickets);
        $l2Tickets = $this->supportManager->getTicketsByLevel($type, 2, '', 'id', 'ASC', false);
        $nbL2Tickets = count($l2Tickets);
        $closedTickets = $this->supportManager->getTicketsByInterventionStatus(
            $type,
            'status_fa',
            '',
            'id',
            'ASC',
            false
        );
        $nbClosedTickets = count($closedTickets);
        $myTickets = $this->supportManager->getTicketsByInterventionUser(
            $type,
            $authenticatedUser,
            '',
            'id',
            'ASC',
            false
        );
        $nbMyTickets =  count($myTickets);

        return array(
            'type' => $type,
            'supportName' => $supportName,
            'nbNewTickets' => $nbNewTickets,
            'nbL1Tickets' => $nbL1Tickets,
            'nbL2Tickets' => $nbL2Tickets,
            'nbClosedTickets' => $nbClosedTickets,
            'nbMyTickets' => $nbMyTickets
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/{type}/new/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="formalibre_admin_support_new",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="creationDate","order"="DESC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportNewAction(
        Type $type,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    )
    {
        $tickets = $this->supportManager->getTicketsWithoutIntervention(
            $type,
            $search,
            $orderedBy,
            $order,
            true,
            $page,
            $max
        );

        return array(
            'tickets' => $tickets,
            'type' => $type,
            'supportName' => 'new',
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/{type}/level/{level}/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="formalibre_admin_support_level",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="creationDate","order"="DESC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportLevelAction(
        Type $type,
        $level,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    )
    {
        $tickets = $this->supportManager->getTicketsByLevel(
            $type,
            $level,
            $search,
            $orderedBy,
            $order,
            true,
            $page,
            $max
        );

        return array(
            'tickets' => $tickets,
            'type' => $type,
            'level' => $level,
            'supportName' => 'level_' . $level,
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/{type}/my/tickets/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="formalibre_admin_support_my_tickets",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="creationDate","order"="DESC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportMyTicketsAction(
        User $authenticatedUser,
        Type $type,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    )
    {
        $tickets = $this->supportManager->getTicketsByInterventionUser(
            $type,
            $authenticatedUser,
            $search,
            $orderedBy,
            $order,
            true,
            $page,
            $max
        );

        return array(
            'tickets' => $tickets,
            'type' => $type,
            'supportName' => 'my_tickets',
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/{type}/archives/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="formalibre_admin_support_archives",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="creationDate","order"="DESC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportArchivesAction(
        Type $type,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    )
    {
        $tickets = $this->supportManager->getTicketsByInterventionStatus(
            $type,
            'status_fa',
            $search,
            $orderedBy,
            $order,
            true,
            $page,
            $max
        );

        return array(
            'tickets' => $tickets,
            'type' => $type,
            'supportName' => 'archives',
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/comments/view",
     *     name="formalibre_admin_ticket_comments_view",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:Support:ticketCommentsModalView.html.twig")
     */
    public function adminTicketCommentsViewAction(Ticket $ticket)
    {
        return array('ticket' => $ticket);
    }
}
