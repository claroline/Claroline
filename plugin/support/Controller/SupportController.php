<?php

namespace FormaLibre\SupportBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\ToolManager;
use FormaLibre\SupportBundle\Entity\Comment;
use FormaLibre\SupportBundle\Entity\Ticket;
use FormaLibre\SupportBundle\Form\CommentType;
use FormaLibre\SupportBundle\Form\TicketType;
use FormaLibre\SupportBundle\Manager\SupportManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Tag("security.secure_service")
 */
class SupportController extends Controller
{
    private $authorization;
    private $eventDispatcher;
    private $formFactory;
    private $platformConfigHandler;
    private $request;
    private $router;
    private $supportManager;
    private $toolManager;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "authorization"         = @DI\Inject("security.authorization_checker"),
     *     "eventDispatcher"       = @DI\Inject("event_dispatcher"),
     *     "formFactory"           = @DI\Inject("form.factory"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "requestStack"          = @DI\Inject("request_stack"),
     *     "router"                = @DI\Inject("router"),
     *     "supportManager"        = @DI\Inject("formalibre.manager.support_manager"),
     *     "toolManager"           = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"            = @DI\Inject("translator")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        EventDispatcherInterface $eventDispatcher,
        FormFactory $formFactory,
        PlatformConfigurationHandler $platformConfigHandler,
        RequestStack $requestStack,
        RouterInterface $router,
        SupportManager $supportManager,
        ToolManager $toolManager,
        TranslatorInterface $translator
    ) {
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->supportManager = $supportManager;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
    }

    /**
     * @EXT\Route(
     *     "/support/ongoing/tickets/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="formalibre_support_ongoing_tickets",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="creationDate","order"="DESC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function ongoingTicketsAction(
        User $user,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    ) {
        $tickets = $this->supportManager->getOngoingTicketsByUser(
            $user,
            $search,
            $orderedBy,
            $order,
            true,
            $page,
            $max
        );

        return [
            'tickets' => $tickets,
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'supportType' => 'ongoing_tickets',
        ];
    }

    /**
     * @EXT\Route(
     *     "/support/archives/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="formalibre_support_archives",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="creationDate","order"="DESC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function archivesAction(
        User $user,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    ) {
        $tickets = $this->supportManager->getClosedTicketsByUser(
            $user,
            $search,
            $orderedBy,
            $order,
            true,
            $page,
            $max
        );

        return [
            'tickets' => $tickets,
            'supportType' => 'archives',
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
        ];
    }

    /**
     * @EXT\Route(
     *     "/support/type/{type}/tabs/active",
     *     name="formalibre_support_type_tabs",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function supportTabsAction(User $user, $type)
    {
        $activeTickets = [];
        $ongoingTickets = $this->supportManager->getOngoingTicketsByUser($user, '', 'id', 'ASC', false);
        $closedTickets = $this->supportManager->getClosedTicketsByUser($user, '', 'id', 'ASC', false);
        $userTickets = $this->supportManager->getTicketsByUser($user);

        foreach ($userTickets as $ticket) {
            if ($ticket->isOpen()) {
                $activeTickets[] = $ticket;
            }
        }

        return [
            'supportType' => $type,
            'nbOngoingTickets' => count($ongoingTickets),
            'nbClosedTickets' => count($closedTickets),
            'activeTickets' => $activeTickets,
        ];
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/tab/close",
     *     name="formalibre_ticket_tab_close",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function ticketTabCloseAction(User $user, Ticket $ticket)
    {
        if ($user->getId() !== $ticket->getUser()->getId()) {
            throw new AccessDeniedException();
        }
        $ticket->setOpen(false);
        $this->supportManager->persistTicket($ticket);

        return new RedirectResponse($this->router->generate('formalibre_support_ongoing_tickets'));
    }

    /**
     * @EXT\Route(
     *     "ticket/create/form",
     *     name="formalibre_ticket_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:Support:ticketCreateModalForm.html.twig")
     */
    public function ticketCreateFormAction(User $user)
    {
        $ticket = new Ticket();
        $ticket->setUser($user);
        $ticket->setContactMail($user->getEmail());
        $phone = $user->getPhone();

        if (!is_null($phone)) {
            $ticket->setContactPhone($phone);
        }
        $form = $this->formFactory->create(new TicketType($this->translator), $ticket);

        return ['form' => $form->createView()];
    }

    /**
     * @EXT\Route(
     *     "ticket/create",
     *     name="formalibre_ticket_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:Support:ticketCreateModalForm.html.twig")
     */
    public function ticketCreateAction(User $user)
    {
        $ticket = new Ticket();
        $ticket->setUser($user);
        $form = $this->formFactory->create(new TicketType($this->translator), $ticket);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->supportManager->initializeTicket($ticket, $user);
            $data = [];
            $data['id'] = $ticket->getId();
            $data['title'] = $ticket->getTitle();
            $data['creationDate'] = $ticket->getCreationDate()->format('d/m/Y H:i');
            $type = $ticket->getType();
            $status = $ticket->getStatus();

            if (!empty($type)) {
                $data['typeName'] = $type->getName();
                $data['typeDescription'] = $type->getDescription();
            }
            if (!empty($status)) {
                $data['statusName'] = $status->getName();
                $data['statusDescription'] = $status->getDescription();
            }

            return new JsonResponse($data, 200);
        } else {
            return ['form' => $form->createView()];
        }
    }

    /**
     * @EXT\Route(
     *     "ticket/{ticket}/edit/form",
     *     name="formalibre_ticket_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:Support:ticketEditModalForm.html.twig")
     */
    public function ticketEditFormAction(User $user, Ticket $ticket)
    {
        $this->checkTicketEditionAccess($user, $ticket);
        $form = $this->formFactory->create(new TicketType($this->translator), $ticket);

        return [
            'form' => $form->createView(),
            'ticket' => $ticket,
        ];
    }

    /**
     * @EXT\Route(
     *     "ticket/{ticket}/edit",
     *     name="formalibre_ticket_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:Support:ticketEditModalForm.html.twig")
     */
    public function ticketEditAction(User $user, Ticket $ticket)
    {
        $this->checkTicketEditionAccess($user, $ticket);
        $form = $this->formFactory->create(new TicketType($this->translator), $ticket);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->supportManager->persistTicket($ticket);
            $this->supportManager->sendTicketMail($user, $ticket, 'ticket_edition');
            $data = [];
            $data['id'] = $ticket->getId();
            $data['title'] = $ticket->getTitle();
            $data['creationDate'] = $ticket->getCreationDate()->format('d/m/Y H:i');
            $type = $ticket->getType();
            $status = $ticket->getStatus();

            if (!empty($type)) {
                $data['typeName'] = $type->getName();
                $data['typeDescription'] = $type->getDescription();
            }
            if (!empty($status)) {
                $data['statusName'] = $status->getName();
                $data['statusDescription'] = $status->getDescription();
            }

            return new JsonResponse($data, 200);
        } else {
            return [
                'form' => $form->createView(),
                'ticket' => $ticket,
            ];
        }
    }

    /**
     * @EXT\Route(
     *     "ticket/{ticket}/delete",
     *     name="formalibre_ticket_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function ticketDeleteAction(User $user, Ticket $ticket)
    {
        $this->checkTicketAccess($user, $ticket);
        $ticketId = $ticket->getId();
        $this->supportManager->removeTicket($ticket, 'user');

        return new JsonResponse($ticketId, 200);
    }

    /**
     * @EXT\Route(
     *     "ticket/{ticket}/hard/delete",
     *     name="formalibre_ticket_hard_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function ticketHardDeleteAction(User $user, Ticket $ticket)
    {
        $this->checkTicketEditionAccess($user, $ticket);
        $ticketId = $ticket->getId();
        $this->supportManager->sendTicketMail($user, $ticket, 'ticket_deletion');
        $this->supportManager->deleteTicket($ticket);

        return new JsonResponse($ticketId, 200);
    }

    /**
     * @EXT\Route(
     *     "ticket/{ticket}/open",
     *     name="formalibre_ticket_open",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function ticketOpenAction(User $user, Ticket $ticket)
    {
        $this->checkTicketAccess($user, $ticket);
        $ticket->setOpen(true);
        $this->supportManager->persistTicket($ticket);

        return new RedirectResponse(
            $this->router->generate('formalibre_ticket_display', ['ticket' => $ticket->getId()])
        );
    }

    /**
     * @EXT\Route(
     *     "ticket/{ticket}/display",
     *     name="formalibre_ticket_display",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function ticketDisplayAction(User $user, Ticket $ticket)
    {
        $this->checkTicketAccess($user, $ticket);

        return [
            'ticket' => $ticket,
            'supportType' => $ticket->getId(),
        ];
    }

    /**
     * @EXT\Route(
     *     "ticket/{ticket}/comment/create/form",
     *     name="formalibre_ticket_comment_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function ticketCommentCreateFormAction(User $user, Ticket $ticket)
    {
        $this->checkTicketAccess($user, $ticket);
        $form = $this->formFactory->create(new CommentType(), new Comment());

        return ['form' => $form->createView(), 'ticket' => $ticket];
    }

    /**
     * @EXT\Route(
     *     "ticket/{ticket}/comment/create",
     *     name="formalibre_ticket_comment_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:Support:ticketCommentCreateForm.html.twig")
     */
    public function ticketCommentCreateAction(User $user, Ticket $ticket)
    {
        $this->checkTicketAccess($user, $ticket);
        $comment = new Comment();
        $form = $this->formFactory->create(new CommentType(), $comment);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $comment->setTicket($ticket);
            $comment->setUser($user);
            $comment->setIsAdmin(false);
            $comment->setCreationDate(new \DateTime());
            $this->supportManager->persistComment($comment);
            $this->supportManager->sendTicketMail(
                $user,
                $ticket,
                'new_comment',
                $comment
            );
            $data = [];
            $data['comment'] = [];
            $data['comment']['id'] = $comment->getId();
            $data['comment']['content'] = $comment->getContent();
            $data['comment']['creationDate'] = $comment->getCreationDate()->format('d/m/Y H:i');
            $data['user']['id'] = $user->getId();
            $data['user']['firstName'] = $user->getFirstName();
            $data['user']['lastName'] = $user->getLastName();
            $data['user']['picture'] = $user->getPicture();

            return new JsonResponse($data, 201);
        } else {
            return ['form' => $form->createView(), 'ticket' => $ticket];
        }
    }

    /**
     * @EXT\Route(
     *     "ticket/from/issue/form",
     *     name="formalibre_ticket_from_issue_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:Support:ticketFromIssueCreateForm.html.twig")
     */
    public function ticketFromIssueCreateFormAction(User $user)
    {
        return ['user' => $user];
    }

    /**
     * @EXT\Route(
     *     "ticket/from/issue",
     *     name="formalibre_ticket_from_issue_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:Support:ticketFromIssueCreateForm.html.twig")
     */
    public function ticketFromIssueCreateAction(User $user)
    {
        $ticket = new Ticket();
        $ticket->setUser($user);
        $title = $this->request->request->get('title', false);
        $email = $this->request->request->get('email', false);
        $phone = $this->request->request->get('phone', false);
        $description = $this->request->request->get('description', false);
        $infos = $this->request->request->get('infos', false);
        $ticket->setTitle($title);
        $ticket->setContactMail($email);
        $completeDescription = '';

        if ($phone) {
            $ticket->setContactPhone($phone);
        }
        if ($description) {
            $completeDescription .= $description;
        }
        if ($infos) {
            if ($description) {
                $completeDescription .= '<hr>';
            }
            $completeDescription .= $infos;
        }
        $ticket->setDescription($completeDescription);
        $type = $this->supportManager->getTypeByName('technical');

        if (!is_null($type)) {
            $ticket->setType($type);
        } else {
            $types = $this->supportManager->getAllTypes();

            if (count($types) > 0) {
                $ticket->setType($types[0]);
            }
        }
        $this->supportManager->initializeTicket($ticket, $user);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "support/forwarded/ticket/comment/create",
     *     name="formalibre_support_forwarded_ticket_comment_create",
     *     options={"expose"=true}
     * )
     */
    public function forwardedTicketCommentCreateAction()
    {
        $token = $this->request->request->get('token', false);
        $this->checkOfficialSupportAccess($token);
        $uuid = $this->request->request->get('uuid', false);
        $content = $this->request->request->get('content', false);
        $ticket = !empty($uuid) ? $this->supportManager->getForwardedTicketByUuid($uuid) : null;
        $isValid = true;
        $httpCode = 201;
        $data = [];

        if (empty($ticket)) {
            $isValid = false;
            $httpCode = 400;
            $data[] = $this->translator->trans('not_valid_msg', ['%param%' => 'uuid'], 'support');
        }
        if (empty($content)) {
            $isValid = false;
            $httpCode = 400;
            $data[] = $this->translator->trans('not_valid_msg', ['%param%' => 'content'], 'support');
        }
        if ($isValid) {
            $comment = new Comment();
            $comment->setTicket($ticket);
            $comment->setContent($content);
            $comment->setCreationDate(new \DateTime());
            $this->supportManager->persistComment($comment);
            $data = [];
            $data['ticket'] = [];
            $data['ticket']['id'] = $ticket->getId();
            $data['ticket']['title'] = $ticket->getTitle();
            $data['ticket']['uuid'] = $ticket->getOfficialUuid();
            $data['comment'] = [];
            $data['comment']['id'] = $comment->getId();
            $data['comment']['content'] = $comment->getContent();
            $data['comment']['creationDate'] = $comment->getCreationDate()->format('d/m/Y H:i');
        }

        return new JsonResponse($data, $httpCode);
    }

    /**
     * @EXT\Route(
     *     "support/forwarded/ticket/status/update",
     *     name="formalibre_support_forwarded_ticket_status_update",
     *     options={"expose"=true}
     * )
     */
    public function forwardedTicketStatusUpdateAction()
    {
        $token = $this->request->request->get('token', false);
        $this->checkOfficialSupportAccess($token);
        $uuid = $this->request->request->get('uuid', false);
        $statusCode = $this->request->request->get('status', false);
        $ticket = empty($uuid) ? null : $this->supportManager->getForwardedTicketByUuid($uuid);
        $status = empty($statusCode) ? null : $this->supportManager->getStatusByCodeInsensitive($statusCode);
        $isValid = true;
        $httpCode = 201;
        $data = [];

        if (empty($ticket)) {
            $isValid = false;
            $httpCode = 400;
            $data[] = $this->translator->trans('not_valid_msg', ['%param%' => 'uuid'], 'support');
        }
        if (empty($status)) {
            $isValid = false;
            $httpCode = 400;
            $data[] = $this->translator->trans('not_valid_msg', ['%param%' => 'status'], 'support');
        }
        if ($isValid) {
            $this->supportManager->createIntervention($ticket, $status);
            $data['ticket'] = [];
            $data['ticket']['id'] = $ticket->getId();
            $data['ticket']['title'] = $ticket->getTitle();
            $data['ticket']['uuid'] = $ticket->getOfficialUuid();
            $data['status'] = [];
            $data['status']['name'] = $this->translator->trans($status->getName(), [], 'support');
            $data['status']['code'] = $status->getCode();
            $data['status']['description'] = $status->getDescription();
        }

        return new JsonResponse($data, $httpCode);
    }

    private function checkTicketAccess(User $user, Ticket $ticket)
    {
        if ($user->getId() !== $ticket->getUser()->getId()) {
            throw new AccessDeniedException();
        }
    }

    private function checkTicketEditionAccess(User $user, Ticket $ticket)
    {
        $status = $ticket->getStatus();
        $interventions = $ticket->getInterventions();

        if ($user->getId() !== $ticket->getUser()->getId() ||
            (!empty($status) && $status->getCode() !== 'NEW') ||
            count($interventions) > 1
        ) {
            throw new AccessDeniedException();
        }
    }

    private function checkOfficialSupportAccess($token)
    {
        $supportToken = $this->platformConfigHandler->hasParameter('support_token') ?
            $this->platformConfigHandler->getParameter('support_token') :
            null;

        if (empty($supportToken) || $supportToken !== $token) {
            throw new AccessDeniedException();
        }

        return $supportToken;
    }
}
