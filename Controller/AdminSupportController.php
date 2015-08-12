<?php

namespace FormaLibre\SupportBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDatasEvent;
use FormaLibre\SupportBundle\Entity\Comment;
use FormaLibre\SupportBundle\Entity\Intervention;
use FormaLibre\SupportBundle\Entity\Status;
use FormaLibre\SupportBundle\Entity\Ticket;
use FormaLibre\SupportBundle\Entity\Type;
use FormaLibre\SupportBundle\Form\CommentEditType;
use FormaLibre\SupportBundle\Form\CommentType;
use FormaLibre\SupportBundle\Form\InterventionStatusType;
use FormaLibre\SupportBundle\Form\InterventionType;
use FormaLibre\SupportBundle\Form\PluginConfigurationType;
use FormaLibre\SupportBundle\Form\StatusType;
use FormaLibre\SupportBundle\Form\TicketTypeChangeType;
use FormaLibre\SupportBundle\Form\TypeType;
use FormaLibre\SupportBundle\Manager\SupportManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('formalibre_support_management_tool')")
 */
class AdminSupportController extends Controller
{
    private $eventDispatcher;
    private $formFactory;
    private $request;
    private $router;
    private $supportManager;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "requestStack"    = @DI\Inject("request_stack"),
     *     "router"          = @DI\Inject("router"),
     *     "supportManager"  = @DI\Inject("formalibre.manager.support_manager"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        FormFactory $formFactory,
        RequestStack $requestStack,
        RouterInterface $router,
        SupportManager $supportManager,
        TranslatorInterface $translator
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->supportManager = $supportManager;
        $this->translator = $translator;
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
        $counters = array();

        foreach ($types as $type) {
            $typeId = $type->getId();
            $counters[$typeId] = array();
            $newTickets = $this->supportManager->getTicketsByLevel($type, 0, '', 'id', 'ASC', false);
            $counters[$typeId]['new'] = count($newTickets);
            $closedTickets = $this->supportManager->getTicketsByLevel($type, -1, '', 'id', 'ASC', false);
            $counters[$typeId]['closed'] = count($closedTickets);
            $l1Tickets = $this->supportManager->getTicketsByLevel($type, 1, '', 'id', 'ASC', false);
            $counters[$typeId]['l1'] = count($l1Tickets);
            $l2Tickets = $this->supportManager->getTicketsByLevel($type, 2, '', 'id', 'ASC', false);
            $counters[$typeId]['l2'] = count($l2Tickets);
        }

        return array('types' => $types, 'counters' => $counters);
    }

    /**
     * @EXT\Route(
     *     "/admin/support/configuration/menu",
     *     name="formalibre_admin_support_configuration_menu",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportConfigurationMenuAction()
    {
        return array();
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/management",
     *     name="formalibre_admin_support_type_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportTypeManagementAction()
    {
        $types = $this->supportManager->getAllTypes();

        return array('types' => $types);
    }

    /**
     * @EXT\Route(
     *     "/admin/support/status/management",
     *     name="formalibre_admin_support_status_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportStatusManagementAction()
    {
        $allStatus = $this->supportManager->getAllStatus();

        return array('allStatus' => $allStatus);
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
        $newTickets = $this->supportManager->getTicketsWithoutInterventionByLevel(
            0,
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
        $closedTickets = $this->supportManager->getTicketsByLevel(
            $type,
            -1,
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
        $nbMyTickets = 0;

        foreach ($myTickets as $ticket) {

            if ($ticket->getLevel() > 0) {
                $nbMyTickets++;
            }
        }

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
        $tickets = $this->supportManager->getTicketsWithoutInterventionByLevel(
            0,
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
        $tickets = $this->supportManager->getTicketsByLevel(
            $type,
            -1,
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
            $this->router->generate('formalibre_admin_ticket_open', array('ticket' => $ticket->getId()))
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
     *     "/admin/ticket/{ticket}/open/comments",
     *     name="formalibre_admin_ticket_open_comments",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminTicketOpenCommentsAction(Ticket $ticket)
    {
        return array('ticket' => $ticket);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/open/interventions",
     *     name="formalibre_admin_ticket_open_interventions",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminTicketOpenInterventionsAction(Ticket $ticket)
    {
        $totalTime = 0;
        $interventions = $ticket->getInterventions();

        foreach ($interventions as $intervention) {
            $duration = $intervention->getDuration();

            if (!is_null($duration)) {
                $totalTime += $duration;
            }
        }

        return array('ticket' => $ticket, 'totalTime' => $totalTime);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/management/info",
     *     name="formalibre_admin_ticket_management_info",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminTicketManagementInfoAction(
        User $authenticatedUser,
        Ticket $ticket
    )
    {
        $interventions = $ticket->getInterventions();
        $lastIntervention = null;
        $nbInterventions = count($interventions);
        $totalTime = 0;

        if ($nbInterventions > 0) {
            $lastIntervention = $interventions[$nbInterventions - 1];

            foreach ($interventions as $intervention) {
                $duration = $intervention->getDuration();

                if (!is_null($duration)) {
                    $totalTime += $duration;
                }
            }
        }

        $unfinishedInterventions = $this->supportManager->getUnfinishedInterventionByTicket($ticket);
        $hasOngoingIntervention = false;
        $ongoingIntervention = null;
        $otherUnfinishedInterventions = array();

        foreach ($unfinishedInterventions as $unfinishedIntervention) {

            if ($unfinishedIntervention->getUser() === $authenticatedUser) {
                $hasOngoingIntervention = true;
                $ongoingIntervention = $unfinishedIntervention;
            } else {
                $otherUnfinishedInterventions[] = $unfinishedIntervention;
            }
        }
        $withCredits = $this->supportManager->getConfigurationCreditOption();

        if ($withCredits) {
            $datasEvent = new GenericDatasEvent($ticket->getUser());
            $this->eventDispatcher->dispatch('formalibre_request_nb_remaining_credits', $datasEvent);
            $response = $datasEvent->getResponse();

            $nbCredits = is_null($response) ? 666 : $response;
        } else {
            $nbCredits = 666;
        }
        $nbHours = (int)($totalTime / 60);
        $nbMinutes = ($nbHours === 0) ? $totalTime : $totalTime % ($nbHours * 60);
        $totalCredits = (5 * $nbHours) + ceil($nbMinutes / 15);

        return array(
            'ticket' => $ticket,
            'currentUser' => $authenticatedUser,
            'unfinishedInterventions' => $otherUnfinishedInterventions,
            'hasOngoingIntervention' => $hasOngoingIntervention,
            'ongoingIntervention' => $ongoingIntervention,
            'lastIntervention' => $lastIntervention,
            'nbCredits' => $nbCredits,
            'totalCredits' => $totalCredits,
            'availableCredits' => $nbCredits,
            'totalTime' => $totalTime,
            'withCredits' => $withCredits
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/intervention/start",
     *     name="formalibre_admin_ticket_intervention_start",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function adminTicketInterventionStartAction(
        User $authenticatedUser,
        Ticket $ticket
    )
    {
        $intervention = new Intervention();
        $intervention->setTicket($ticket);
        $intervention->setUser($authenticatedUser);
        $intervention->setStartDate(new \DateTime());
        $this->supportManager->persistIntervention($intervention);

        return new JsonResponse(array('id' => $intervention->getId()), 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/intervention/{intervention}/stop",
     *     name="formalibre_admin_ticket_intervention_stop",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function adminTicketInterventionStopAction(Intervention $intervention)
    {
        $endDate = new \DateTime();
        $startDate = $intervention->getStartDate();
        $startDateTimestamp = $startDate->format('U');
        $endDateTimestamp = $endDate->format('U');
        $duration = ceil(($endDateTimestamp - $startDateTimestamp) / 60);
        $intervention->setEndDate($endDate);
        $intervention->setDuration($duration);
        $this->supportManager->persistIntervention($intervention);

        $status = $intervention->getStatus();

        if (!is_null($status) && $status->getCode() === 'FA') {
            $ticket = $intervention->getTicket();
            $ticket->setLevel(-1);
            $this->supportManager->persistTicket($ticket);
        }

        return new JsonResponse(array('id' => $intervention->getId()), 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/type/change/form",
     *     name="formalibre_admin_ticket_type_change_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminTicketTypeChangeModalForm.html.twig")
     */
    public function adminTicketTypeChangeFormAction(Ticket $ticket)
    {
        $form = $this->formFactory->create(new TicketTypeChangeType($ticket), $ticket);

        return array('form' => $form->createView(), 'ticket' => $ticket);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/type/change",
     *     name="formalibre_admin_ticket_type_change",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminTicketTypeChangeModalForm.html.twig")
     */
    public function adminTicketTypeChangeAction(Ticket $ticket)
    {
        $form = $this->formFactory->create(new TicketTypeChangeType($ticket), $ticket);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->supportManager->persistTicket($ticket);

            return new JsonResponse($ticket->getId(), 200);
        } else {

            return array('form' => $form->createView(), 'ticket' => $ticket);
        }
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
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminTicketCommentEditModalForm.html.twig")
     */
    public function adminTicketCommentEditFormAction(Comment $comment)
    {
        $form = $this->formFactory->create(new CommentEditType(), $comment);

        return array('form' => $form->createView(), 'comment' => $comment);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/comment/{comment}/edit",
     *     name="formalibre_admin_ticket_comment_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminTicketCommentEditModalForm.html.twig")
     */
    public function adminTicketCommentEditAction(Comment $comment)
    {
        $form = $this->formFactory->create(new CommentEditType(), $comment);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $comment->setEditionDate(new \DateTime());
            $this->supportManager->persistComment($comment);

            return new JsonResponse('success', 200);
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

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/intervention/create/form",
     *     name="formalibre_admin_ticket_intervention_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminTicketInterventionCreateFormAction(
        User $authenticatedUser,
        Ticket $ticket
    )
    {
        $intervention = new Intervention();
        $now = new \DateTime();
        $intervention->setStartDate($now);
        $intervention->setEndDate($now);
        $intervention->setDuration(0);
        $form = $this->formFactory->create(
            new InterventionType($authenticatedUser),
            $intervention
        );

        return array('form' => $form->createView(), 'ticket' => $ticket);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/intervention/create",
     *     name="formalibre_admin_ticket_intervention_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminTicketInterventionCreateForm.html.twig")
     */
    public function adminTicketInterventionCreateAction(
        User $authenticatedUser,
        Ticket $ticket
    )
    {
        $intervention = new Intervention();
        $intervention->setTicket($ticket);
        $intervention->setUser($authenticatedUser);
        $now = new \DateTime();
        $intervention->setStartDate($now);
        $form = $this->formFactory->create(
            new InterventionType($authenticatedUser),
            $intervention
        );
        $form->handleRequest($this->request);
        $timeType = $form->get('computeTimeMode')->getData();
        $startDate = $intervention->getStartDate();

        if (!is_null($timeType) && !is_null($startDate)) {
            $startDateTimestamp = $startDate->format('U');

            if ($timeType === 0) {
                $endDate = $intervention->getEndDate();

                if (!is_null($endDate)) {
                    $endDateTimestamp = $endDate->format('U');
                    $duration = ceil(($endDateTimestamp - $startDateTimestamp) / 60);
                    $intervention->setDuration($duration);
                } else {
                    $form->addError(
                        new FormError(
                            $this->translator->trans('end_date_is_required', array(), 'support')
                        )
                    );
                }
            } elseif ($timeType === 1) {
                $duration = $intervention->getDuration();

                if (!is_null($duration)) {
                    $endDateTimestamp = $startDateTimestamp + ($duration * 60);
                    $endDate = new \DateTime();
                    $endDate->setTimestamp($endDateTimestamp);
                    $intervention->setEndDate($endDate);
                } else {
                    $form->addError(
                        new FormError(
                            $this->translator->trans('duration_is_required', array(), 'support')
                        )
                    );
                }
            }
        }

        if ($form->isValid()) {
            $this->supportManager->persistIntervention($intervention);

            return new RedirectResponse(
                $this->router->generate(
                    'formalibre_admin_ticket_open_interventions',
                    array('ticket' => $ticket->getId())
                )
            );
        } else {

            return array('form' => $form->createView(), 'ticket' => $ticket);
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/intervention/{intervention}/edit/form",
     *     name="formalibre_admin_ticket_intervention_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminTicketInterventionEditFormAction(Intervention $intervention)
    {
        $form = $this->formFactory->create(
            new InterventionType($intervention->getUser()),
            $intervention
        );

        return array(
            'form' => $form->createView(),
            'intervention' => $intervention,
            'ticket' => $intervention->getTicket()
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/intervention/{intervention}/edit",
     *     name="formalibre_admin_ticket_intervention_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminTicketInterventionEditForm.html.twig")
     */
    public function adminTicketInterventionEditAction(Intervention $intervention)
    {
        $ticket = $intervention->getTicket();
        $form = $this->formFactory->create(
            new InterventionType($intervention->getUser()),
            $intervention
        );
        $form->handleRequest($this->request);
        $timeType = $form->get('computeTimeMode')->getData();
        $startDate = $intervention->getStartDate();

        if (!is_null($timeType) && !is_null($startDate)) {
            $startDateTimestamp = $startDate->format('U');

            if ($timeType === 0) {
                $endDate = $intervention->getEndDate();

                if (!is_null($endDate)) {
                    $endDateTimestamp = $endDate->format('U');
                    $duration = ceil(($endDateTimestamp - $startDateTimestamp) / 60);
                    $intervention->setDuration($duration);
                } else {
                    $form->addError(
                        new FormError(
                            $this->translator->trans('end_date_is_required', array(), 'support')
                        )
                    );
                }
            } elseif ($timeType === 1) {
                $duration = $intervention->getDuration();

                if (!is_null($duration)) {
                    $endDateTimestamp = $startDateTimestamp + ($duration * 60);
                    $endDate = new \DateTime();
                    $endDate->setTimestamp($endDateTimestamp);
                    $intervention->setEndDate($endDate);
                } else {
                    $form->addError(
                        new FormError(
                            $this->translator->trans('duration_is_required', array(), 'support')
                        )
                    );
                }
            }
        }

        if ($form->isValid()) {
            $this->supportManager->persistIntervention($intervention);

            return new RedirectResponse(
                $this->router->generate(
                    'formalibre_admin_ticket_open_interventions',
                    array('ticket' => $ticket->getId())
                )
            );
        } else {

            return array(
                'form' => $form->createView(),
                'intervention' => $intervention,
                'ticket' => $ticket
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/intervention/{intervention}/delete",
     *     name="formalibre_admin_ticket_intervention_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function adminTicketInterventionDeleteAction(Intervention $intervention)
    {
        $this->supportManager->deleteIntervention($intervention);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/intervention/{intervention}/status/edit/form",
     *     name="formalibre_admin_ticket_intervention_status_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminInterventionStatusEditModalForm.html.twig")
     */
    public function adminTicketInterventionStatusEditFormAction(Intervention $intervention)
    {
        $form = $this->formFactory->create(
            new InterventionStatusType(),
            $intervention
        );

        return array('form' => $form->createView(), 'intervention' => $intervention);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/intervention/{intervention}/status/edit",
     *     name="formalibre_admin_ticket_intervention_status_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminInterventionStatusEditModalForm.html.twig")
     */
    public function adminTicketInterventionStatusEditAction(Intervention $intervention)
    {
        $form = $this->formFactory->create(
            new InterventionStatusType(),
            $intervention
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->supportManager->persistIntervention($intervention);

            return new JsonResponse($intervention->getId(), 200);
        } else {

            return array('form' => $form->createView(), 'intervention' => $intervention);
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/create/form",
     *     name="formalibre_admin_support_type_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportTypeCreateModalForm.html.twig")
     */
    public function adminSupportTypeCreateFormAction()
    {
        $form = $this->formFactory->create(new TypeType(), new Type());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/create",
     *     name="formalibre_admin_support_type_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportTypeCreateModalForm.html.twig")
     */
    public function adminSupportTypeCreateAction()
    {
        $type = new Type();
        $form = $this->formFactory->create(new TypeType(), $type);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->supportManager->persistType($type);

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/{type}/edit/form",
     *     name="formalibre_admin_support_type_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportTypeEditModalForm.html.twig")
     */
    public function adminSupportTypeEditFormAction(Type $type)
    {
        $form = $this->formFactory->create(new TypeType(), $type);

        return array('form' => $form->createView(), 'type' => $type);
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/{type}/edit",
     *     name="formalibre_admin_support_type_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportTypeEditModalForm.html.twig")
     */
    public function adminSupportTypeEditAction(Type $type)
    {
        $form = $this->formFactory->create(new TypeType(), $type);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->supportManager->persistType($type);

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView(), 'type' => $type);
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/{type}/delete",
     *     name="formalibre_admin_support_type_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function adminSupportTypeDeleteAction(Type $type)
    {
        $this->supportManager->deleteType($type);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/support/status/create/form",
     *     name="formalibre_admin_support_status_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportStatusCreateModalForm.html.twig")
     */
    public function adminSupportStatusCreateFormAction()
    {
        $form = $this->formFactory->create(new StatusType(), new Status());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/admin/support/status/create",
     *     name="formalibre_admin_support_status_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportStatusCreateModalForm.html.twig")
     */
    public function adminSupportStatusCreateAction()
    {
        $status = new Status();
        $form = $this->formFactory->create(new StatusType(), $status);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $lastOrder = $this->supportManager->getOrderOfLastStatus();

            if (is_null($lastOrder['order_max'])) {
                $status->setOrder(1);
            } else {
                $status->setOrder($lastOrder['order_max'] + 1);
            }
            $this->supportManager->persistStatus($status);

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/support/status/{status}/edit/form",
     *     name="formalibre_admin_support_status_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportStatusEditModalForm.html.twig")
     */
    public function adminSupportStatusEditFormAction(Status $status)
    {
        $form = $this->formFactory->create(new StatusType(), $status);

        return array('form' => $form->createView(), 'status' => $status);
    }

    /**
     * @EXT\Route(
     *     "/admin/support/status/{status}/edit",
     *     name="formalibre_admin_support_status_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportStatusEditModalForm.html.twig")
     */
    public function adminSupportStatusEditAction(Status $status)
    {
        $form = $this->formFactory->create(new StatusType(), $status);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->supportManager->persistStatus($status);

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView(), 'status' => $status);
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/support/status/{status}/delete",
     *     name="formalibre_admin_support_status_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function adminSupportStatusDeleteAction(Status $status)
    {
        $this->supportManager->deleteStatus($status);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/support/status/{status}/reorder/next/{nextStatusId}",
     *     name="formalibre_admin_support_status_reorder",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function adminSupportStatusReorderAction(Status $status, $nextStatusId)
    {
        $this->supportManager->reorderStatus($status, $nextStatusId);

        return new JsonResponse('success', 200);
    }


    /********************************
     * Plugin configuration methods *
     ********************************/

    /**
     * @EXT\Route(
     *     "/plugin/configure/form",
     *     name="formalibre_support_plugin_configure_form"
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function pluginConfigureFormAction()
    {
        $config = $this->supportManager->getConfiguration();
        $details = $config->getDetails();

        $form = $this->formFactory->create(new PluginConfigurationType($details));

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/plugin/configure",
     *     name="formalibre_support_plugin_configure"
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:pluginConfigureForm.html.twig")
     */
    public function pluginConfigureAction()
    {
        $config = $this->supportManager->getConfiguration();
        $details = $config->getDetails();

        $form = $this->formFactory->create(new PluginConfigurationType($details));
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $details['with_credits'] = $form->get('withCredits')->getData();
            $config->setDetails($details);
            $this->supportManager->persistConfiguration($config);

            return new RedirectResponse(
                $this->router->generate('claro_admin_plugins')
            );
        } else {

            return array('form' => $form->createView());
        }
    }
}
