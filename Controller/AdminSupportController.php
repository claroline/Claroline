<?php

namespace FormaLibre\SupportBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use FormaLibre\SupportBundle\Entity\Comment;
use FormaLibre\SupportBundle\Entity\Ticket;
use FormaLibre\SupportBundle\Entity\Type;
use FormaLibre\SupportBundle\Form\CommentType;
use FormaLibre\SupportBundle\Manager\SupportManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('formalibre_support_management_tool')")
 */
class AdminSupportController extends Controller
{
    private $formFactory;
    private $request;
    private $router;
    private $supportManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"    = @DI\Inject("form.factory"),
     *     "requestStack"   = @DI\Inject("request_stack"),
     *     "router"         = @DI\Inject("router"),
     *     "supportManager" = @DI\Inject("formalibre.manager.support_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        RequestStack $requestStack,
        RouterInterface $router,
        SupportManager $supportManager
    )
    {
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
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
        $lastStatus = array();

        foreach ($tickets as $ticket) {
            $interventions = $ticket->getInterventions();
            $reverseInterventions = array_reverse($interventions);

            foreach ($reverseInterventions as $intervention) {
                $status = $intervention->getStatus();

                if (!is_null($status)) {
                    $lastStatus[$ticket->getId()] = $status;
                    break;
                }
            }
        }

        return array(
            'tickets' => $tickets,
            'type' => $type,
            'level' => $level,
            'lastStatus' => $lastStatus,
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

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/interventions/view",
     *     name="formalibre_admin_ticket_interventions_view",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminTicketInterventionsModalView.html.twig")
     */
    public function adminTicketInterventionsViewAction(Ticket $ticket)
    {
        return array('ticket' => $ticket);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/delete",
     *     name="formalibre_admin_ticket_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function adminTicketDeleteAction(Ticket $ticket)
    {
        $this->supportManager->deleteTicket($ticket);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/new/open",
     *     name="formalibre_admin_ticket_new_open",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function adminNewTicketOpenAction(User $authenticatedUser, Ticket $ticket)
    {
        if ($ticket->getLevel() === 0) {
            $this->supportManager->startTicket($ticket, $authenticatedUser);
        }

        return new RedirectResponse(
            $this->router->generate('formalibre_support_index')
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/open",
     *     name="formalibre_admin_ticket_open",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminTicketOpenAction(Ticket $ticket)
    {
        return array('ticket' => $ticket);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/comment/create/form",
     *     name="formalibre_admin_ticket_comment_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminTicketCommentCreateFormAction(Ticket $ticket)
    {
        $form = $this->formFactory->create(new CommentType(), new Comment());

        return array('form' => $form->createView(), 'ticket' => $ticket);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/comment/create",
     *     name="formalibre_admin_ticket_comment_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminTicketCommentCreateForm.html.twig")
     */
    public function adminTicketCommentCreateAction(User $authenticatedUser, Ticket $ticket)
    {
        $comment = new Comment();
        $form = $this->formFactory->create(new CommentType(), $comment);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $comment->setTicket($ticket);
            $comment->setUser($authenticatedUser);
            $comment->setIsAdmin(true);
            $comment->setCreationDate(new \DateTime());
            $this->supportManager->persistComment($comment);

            return new JsonResponse('success', 201);
        } else {

            return array('form' => $form->createView(), 'ticket' => $ticket);
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/comment/{comment}/edit/form",
     *     name="formalibre_admin_ticket_comment_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:Support:adminTicketCommentEditModalForm.html.twig")
     */
    public function adminTicketCommentEditFormAction(Comment $comment)
    {
        $form = $this->formFactory->create(new CommentType(), $comment);

        return array('form' => $form->createView(), 'comment' => $comment);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/comment/{comment}/edit",
     *     name="formalibre_admin_ticket_comment_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:Support:adminTicketCommentEditModalForm.html.twig")
     */
    public function adminTicketCommentEditAction(Comment $comment)
    {
        $form = $this->formFactory->create(new CommentType(), $comment);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $comment->setEditionDate(new \DateTime());
            $this->supportManager->persistComment($comment);

            return new JsonResponse('success', 204);
        } else {

            return array('form' => $form->createView(), 'comment' => $comment);
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/comment/{comment}/delete",
     *     name="formalibre_admin_ticket_comment_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function adminTicketCommentDeleteAction(Comment $comment)
    {
        $this->supportManager->deleteComment($comment);

        return new JsonResponse('success', 200);
    }
}
