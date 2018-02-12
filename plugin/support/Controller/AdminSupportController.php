<?php

namespace FormaLibre\SupportBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\UserManager;
use FormaLibre\SupportBundle\Entity\Comment;
use FormaLibre\SupportBundle\Entity\Status;
use FormaLibre\SupportBundle\Entity\Ticket;
use FormaLibre\SupportBundle\Entity\TicketUser;
use FormaLibre\SupportBundle\Entity\Type;
use FormaLibre\SupportBundle\Form\AdminTicketType;
use FormaLibre\SupportBundle\Form\CommentEditType;
use FormaLibre\SupportBundle\Form\CommentType;
use FormaLibre\SupportBundle\Form\StatusType;
use FormaLibre\SupportBundle\Form\TicketInterventionType;
use FormaLibre\SupportBundle\Form\TypeType;
use FormaLibre\SupportBundle\Manager\SupportManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('formalibre_support_management_tool')")
 */
class AdminSupportController extends Controller
{
    private $eventDispatcher;
    private $formFactory;
    private $platformConfigHandler;
    private $request;
    private $router;
    private $supportManager;
    private $translator;
    private $userManager;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher"       = @DI\Inject("event_dispatcher"),
     *     "formFactory"           = @DI\Inject("form.factory"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "requestStack"          = @DI\Inject("request_stack"),
     *     "router"                = @DI\Inject("router"),
     *     "supportManager"        = @DI\Inject("formalibre.manager.support_manager"),
     *     "translator"            = @DI\Inject("translator"),
     *     "userManager"           = @DI\Inject("claroline.manager.user_manager")
     * })
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        FormFactory $formFactory,
        PlatformConfigurationHandler $platformConfigHandler,
        RequestStack $requestStack,
        RouterInterface $router,
        SupportManager $supportManager,
        TranslatorInterface $translator,
        UserManager $userManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->supportManager = $supportManager;
        $this->translator = $translator;
        $this->userManager = $userManager;
    }

    /**
     * @EXT\Route(
     *     "/admin/support/index/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="formalibre_admin_support_ongoing_tickets",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="creationDate","order"="DESC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportOngoingTicketsAction($search = '', $page = 1, $max = 50, $orderedBy = 'creationDate', $order = 'DESC')
    {
        $tickets = $this->supportManager->getOngoingTickets($search, $orderedBy, $order, true, $page, $max);
        $supportToken = $this->platformConfigHandler->hasParameter('support_token') ?
            $this->platformConfigHandler->getParameter('support_token') :
            null;

        return [
            'tickets' => $tickets,
            'title' => 'ongoing_tickets',
            'supportType' => 'ongoing_tickets',
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'supportToken' => $supportToken,
        ];
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/{type}/tabs/active",
     *     name="formalibre_admin_support_type_tabs",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportTabsAction(User $user, $type)
    {
        $ongoingTickets = $this->supportManager->getOngoingTickets('', 'id', 'ASC', false);
        $myTickets = $this->supportManager->getMyTickets($user, '', 'id', 'ASC', false);
        $closedTickets = $this->supportManager->getClosedTickets('', 'id', 'ASC', false);
        $forwardedTickets = $this->supportManager->getOngoingForwardedTickets('', 'id', 'ASC', false);
        $activeTicketUsers = $this->supportManager->getActiveTicketUserByUser($user);
        $supportToken = $this->platformConfigHandler->hasParameter('support_token') ?
            $this->platformConfigHandler->getParameter('support_token') :
            null;

        return [
            'supportType' => $type,
            'nbOngoingTickets' => count($ongoingTickets),
            'nbMyTickets' => count($myTickets),
            'nbClosedTickets' => count($closedTickets),
            'nbForwardedTickets' => count($forwardedTickets),
            'activeTicketUsers' => $activeTicketUsers,
            'supportToken' => $supportToken,
        ];
    }

    /**
     * @EXT\Route(
     *     "/admin/support/my/tickets/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="formalibre_admin_support_my_tickets",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="creationDate","order"="DESC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportMyTicketsAction(
        User $user,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    ) {
        $tickets = $this->supportManager->getMyTickets($user, $search, $orderedBy, $order, true, $page, $max);
        $supportToken = $this->platformConfigHandler->hasParameter('support_token') ?
            $this->platformConfigHandler->getParameter('support_token') :
            null;

        return [
            'tickets' => $tickets,
            'title' => 'my_tickets',
            'supportType' => 'my_tickets',
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'supportToken' => $supportToken,
        ];
    }

    /**
     * @EXT\Route(
     *     "/admin/support/archives/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="formalibre_admin_support_archives",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="creationDate","order"="DESC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportArchivesAction(
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    ) {
        $tickets = $this->supportManager->getClosedTickets($search, $orderedBy, $order, true, $page, $max);
        $supportToken = $this->platformConfigHandler->hasParameter('support_token') ?
            $this->platformConfigHandler->getParameter('support_token') :
            null;

        return [
            'tickets' => $tickets,
            'title' => 'archives',
            'supportType' => 'archives',
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'supportToken' => $supportToken,
        ];
    }

    /**
     * @EXT\Route(
     *     "/admin/support/forwarded/tickets/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="formalibre_admin_support_forwarded_tickets",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="creationDate","order"="DESC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportForwardedTicketsAction(
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'creationDate',
        $order = 'DESC'
    ) {
        $tickets = $this->supportManager->getOngoingForwardedTickets($search, $orderedBy, $order, true, $page, $max);
        $supportToken = $this->platformConfigHandler->hasParameter('support_token') ?
            $this->platformConfigHandler->getParameter('support_token') :
            null;

        return [
            'tickets' => $tickets,
            'title' => 'forwarded_tickets',
            'supportType' => 'forwarded_tickets',
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'supportToken' => $supportToken,
        ];
    }

    /**
     * @EXT\Route(
     *     "/admin/support/types/management",
     *     name="formalibre_admin_support_types_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportTypesManagementAction()
    {
        $types = $this->supportManager->getAllTypes('', 'id');

        return [
            'types' => $types,
            'title' => 'types_management',
        ];
    }

    /**
     * @EXT\Route(
     *     "/admin/support/status/management",
     *     name="formalibre_admin_support_status_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportStatusManagementAction()
    {
        $allStatus = $this->supportManager->getAllStatus();

        return [
            'allStatus' => $allStatus,
            'title' => 'status_management',
        ];
    }

    /**
     * @EXT\Route(
     *     "/admin/support/notifications/management",
     *     name="formalibre_admin_support_notifications_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportNotificationsManagementAction()
    {
        $contacts = [];
        $config = $this->supportManager->getConfiguration();
        $userIds = $config->getContacts();
        $users = $this->userManager->getUsersByIds($userIds);

        foreach ($users as $user) {
            $contacts[] = [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
            ];
        }

        return [
            'contacts' => $contacts,
            'title' => 'notifications_management',
            'supportConfig' => $config,
        ];
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/open",
     *     name="formalibre_admin_ticket_open",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function adminTicketOpenAction(User $user, Ticket $ticket)
    {
        $this->supportManager->activateTicketUser($ticket, $user);

        return new RedirectResponse(
            $this->router->generate('formalibre_admin_ticket_display', ['ticket' => $ticket->getId()])
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/display",
     *     name="formalibre_admin_ticket_display",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminTicketDisplayAction(Ticket $ticket)
    {
        $supportToken = $this->platformConfigHandler->hasParameter('support_token') ?
            $this->platformConfigHandler->getParameter('support_token') :
            null;

        return [
            'ticket' => $ticket,
            'title' => $ticket->getTitle(),
            'supportType' => $ticket->getId(),
            'supportToken' => $supportToken,
        ];
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/delete",
     *     name="formalibre_admin_ticket_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function adminTicketDeleteAction(Ticket $ticket)
    {
        $ticketId = $ticket->getId();
        $this->supportManager->removeTicket($ticket, 'admin');

        return new JsonResponse($ticketId, 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/closing",
     *     name="formalibre_ticket_closing",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function adminTicketClosingAction(User $user, Ticket $ticket)
    {
        $this->supportManager->closeTicket($ticket, $user);

        return new JsonResponse($ticket->getId(), 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/my/ticket/{ticket}/remove",
     *     name="formalibre_admin_my_ticket_remove",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function adminMyTicketRemoveAction(User $user, Ticket $ticket)
    {
        $this->supportManager->deleteTicketUser($ticket, $user);

        return new JsonResponse($ticket->getId(), 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/user/{ticketUser}/close",
     *     name="formalibre_admin_ticket_user_close",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminTicketTabCloseAction(User $user, TicketUser $ticketUser)
    {
        if ($user->getId() !== $ticketUser->getUser()->getId()) {
            throw new AccessDeniedException();
        }
        $this->supportManager->deactivateTicketUser($ticketUser);

        return new RedirectResponse($this->router->generate('formalibre_admin_support_ongoing_tickets'));
    }

    /**
     * @EXT\Route(
     *     "/admin/support/contacts/add",
     *     name="formalibre_admin_support_contacts_add",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function adminSupportContactsAddAction()
    {
        $config = $this->supportManager->getConfiguration();
        $contacts = $config->getContacts();
        $toAdd = $this->request->request->get('contactIds', false);

        foreach ($toAdd as $userId) {
            $contacts[] = intval($userId);
        }
        $config->setContacts($contacts);
        $this->supportManager->persistConfiguration($config);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/support/contact/{contactId}/remove",
     *     name="formalibre_admin_support_contact_remove",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function adminSupportContactRemoveAction($contactId)
    {
        $config = $this->supportManager->getConfiguration();
        $contacts = $config->getContacts();
        $key = array_search($contactId, $contacts);

        if ($key !== false) {
            unset($contacts[$key]);
            $config->setContacts($contacts);
            $this->supportManager->persistConfiguration($config);
        }

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/support/notify/update",
     *     name="formalibre_admin_support_notify_update",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function adminSupportNotifyUpdateAction()
    {
        $config = $this->supportManager->getConfiguration();
        $type = $this->request->request->get('notifyType', false);
        $value = boolval($this->request->request->get('notifyValue', false));
        $config->setNotify($type, $value);
        $this->supportManager->persistConfiguration($config);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/comment/type/{type}/create/form",
     *     name="formalibre_admin_ticket_comment_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminTicketCommentCreateFormAction(Ticket $ticket, $type)
    {
        $form = $this->formFactory->create(new CommentType(intval($type)), new Comment());

        return [
            'form' => $form->createView(),
            'ticket' => $ticket,
            'type' => $type,
        ];
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/{ticket}/comment/type/{type}/create",
     *     name="formalibre_admin_ticket_comment_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminTicketCommentCreateForm.html.twig")
     */
    public function adminTicketCommentCreateAction(User $user, Ticket $ticket, $type)
    {
        $isForwarded = $ticket->isForwarded();
        $comment = new Comment();
        $form = $this->formFactory->create(new CommentType(intval($type)), $comment);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            if ($isForwarded) {
                $supportToken = $this->checkOfficialSupportAccess();
                $platformUrl = $this->platformConfigHandler->hasParameter('support_platform_url') ?
                    $this->platformConfigHandler->getParameter('support_platform_url') :
                    '';
                $url = 'https://api.claroline.cloud/cc/support/'.$supportToken.'/tickets/'.$ticket->getOfficialUuid().'/comments';
                $postDataString = '{
                    "comment":"'.urlencode($comment->getContent()).'",
                    "platformUrl":"'.$platformUrl.'"
                }';
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataString);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $returnedData = (array) json_decode(curl_exec($ch));
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode !== 201) {
                    $data = is_array($returnedData) && count($returnedData) > 0 ?
                        $returnedData[0] :
                        $this->translator->trans('message_sending_has_failed', [], 'support');
                }
            } else {
                $httpCode = 201;
            }
            if ($httpCode === 201) {
                $comment->setTicket($ticket);
                $comment->setUser($user);
                $comment->setIsAdmin(true);
                $comment->setType($type);
                $comment->setCreationDate(new \DateTime());
                $this->supportManager->persistComment($comment);

                if (!$isForwarded) {
                    switch ($type) {
                        case Comment::PUBLIC_COMMENT:
                            $this->supportManager->sendTicketMail(
                                $user,
                                $comment->getTicket(),
                                'new_admin_comment',
                                $comment
                            );
                            break;
                        case Comment::PRIVATE_COMMENT:
                            $this->supportManager->sendTicketMail(
                                $user,
                                $comment->getTicket(),
                                'new_internal_note',
                                $comment
                            );
                            break;
                    }
                }
                $data = [];
                $data['comment'] = [];
                $data['comment']['id'] = $comment->getId();
                $data['comment']['content'] = $comment->getContent();
                $data['comment']['type'] = $comment->getType();
                $data['comment']['creationDate'] = $comment->getCreationDate()->format('d/m/Y H:i');
                $data['user']['id'] = $user->getId();
                $data['user']['firstName'] = $user->getFirstName();
                $data['user']['lastName'] = $user->getLastName();
                $data['user']['picture'] = $user->getPicture();
                $data['editable'] = !$isForwarded;
            }

            return new JsonResponse($data, $httpCode);
        } else {
            return [
                'form' => $form->createView(),
                'ticket' => $ticket,
                'type' => $type,
            ];
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/comment/{comment}/type/{type}/edit/form",
     *     name="formalibre_admin_ticket_comment_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminTicketCommentEditModalForm.html.twig")
     */
    public function adminTicketCommentEditFormAction(Comment $comment, $type)
    {
        $form = $this->formFactory->create(new CommentEditType(intval($type)), $comment);

        return [
            'form' => $form->createView(),
            'comment' => $comment,
            'type' => $type,
        ];
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/comment/{comment}/type/{type}/edit",
     *     name="formalibre_admin_ticket_comment_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminTicketCommentEditModalForm.html.twig")
     */
    public function adminTicketCommentEditAction(User $user, Comment $comment, $type)
    {
        $form = $this->formFactory->create(new CommentEditType(intval($type)), $comment);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $comment->setEditionDate(new \DateTime());
            $this->supportManager->persistComment($comment);

            switch ($type) {
                case Comment::PUBLIC_COMMENT:
                    $this->supportManager->sendTicketMail(
                        $user,
                        $comment->getTicket(),
                        'new_admin_comment',
                        $comment
                    );
                    break;
                case Comment::PRIVATE_COMMENT:
                    $this->supportManager->sendTicketMail(
                        $user,
                        $comment->getTicket(),
                        'new_internal_note',
                        $comment
                    );
                    break;
            }

            return new JsonResponse(
                ['id' => $comment->getId(), 'content' => $comment->getContent(), 'type' => $comment->getType()],
                200
            );
        } else {
            return [
                'form' => $form->createView(),
                'comment' => $comment,
                'type' => $type,
            ];
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/comment/{comment}/delete",
     *     name="formalibre_admin_ticket_comment_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function adminTicketCommentDeleteAction(Comment $comment)
    {
        $this->supportManager->deleteComment($comment);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/create/form",
     *     name="formalibre_admin_support_type_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportTypeCreateModalForm.html.twig")
     */
    public function adminSupportTypeCreateFormAction()
    {
        $form = $this->formFactory->create(new TypeType(), new Type());

        return ['form' => $form->createView()];
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/create",
     *     name="formalibre_admin_support_type_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportTypeCreateModalForm.html.twig")
     */
    public function adminSupportTypeCreateAction()
    {
        $type = new Type();
        $form = $this->formFactory->create(new TypeType(), $type);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->supportManager->persistType($type);

            return new JsonResponse(
                ['id' => $type->getId(), 'name' => $type->getName(), 'description' => $type->getDescription()],
                200
            );
        } else {
            return ['form' => $form->createView()];
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/{type}/edit/form",
     *     name="formalibre_admin_support_type_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportTypeEditModalForm.html.twig")
     */
    public function adminSupportTypeEditFormAction(Type $type)
    {
        $form = $this->formFactory->create(new TypeType($type->isLocked()), $type);

        return ['form' => $form->createView(), 'type' => $type];
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/{type}/edit",
     *     name="formalibre_admin_support_type_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportTypeEditModalForm.html.twig")
     */
    public function adminSupportTypeEditAction(Type $type)
    {
        $form = $this->formFactory->create(new TypeType($type->isLocked()), $type);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->supportManager->persistType($type);

            return new JsonResponse(
                [
                    'id' => $type->getId(),
                    'name' => $type->getName(),
                    'description' => $type->getDescription(),
                    'locked' => $type->isLocked(),
                ],
                200
            );
        } else {
            return ['form' => $form->createView(), 'type' => $type];
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/support/type/{type}/delete",
     *     name="formalibre_admin_support_type_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function adminSupportTypeDeleteAction(Type $type)
    {
        if ($type->isLocked()) {
            throw new AccessDeniedException();
        }
        $this->supportManager->deleteType($type);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/support/status/create/form",
     *     name="formalibre_admin_support_status_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportStatusCreateModalForm.html.twig")
     */
    public function adminSupportStatusCreateFormAction()
    {
        $form = $this->formFactory->create(new StatusType(), new Status());

        return ['form' => $form->createView()];
    }

    /**
     * @EXT\Route(
     *     "/admin/support/status/create",
     *     name="formalibre_admin_support_status_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
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

            return new JsonResponse(
                [
                    'id' => $status->getId(),
                    'name' => $status->getName(),
                    'description' => $status->getDescription(),
                    'code' => $status->getCode(),
                    'order' => $status->getOrder(),
                    'locked' => $status->isLocked(),
                ],
                200
            );
        } else {
            return ['form' => $form->createView()];
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/support/status/{status}/edit/form",
     *     name="formalibre_admin_support_status_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportStatusEditModalForm.html.twig")
     */
    public function adminSupportStatusEditFormAction(Status $status)
    {
        $form = $this->formFactory->create(new StatusType($status->isLocked()), $status);

        return ['form' => $form->createView(), 'status' => $status];
    }

    /**
     * @EXT\Route(
     *     "/admin/support/status/{status}/edit",
     *     name="formalibre_admin_support_status_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportStatusEditModalForm.html.twig")
     */
    public function adminSupportStatusEditAction(Status $status)
    {
        $form = $this->formFactory->create(new StatusType($status->isLocked()), $status);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->supportManager->persistStatus($status);

            return new JsonResponse(
                [
                    'id' => $status->getId(),
                    'name' => $status->getName(),
                    'description' => $status->getDescription(),
                    'code' => $status->getCode(),
                    'order' => $status->getOrder(),
                    'locked' => $status->isLocked(),
                ],
                200
            );
        } else {
            return ['form' => $form->createView(), 'status' => $status];
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/support/status/{status}/delete",
     *     name="formalibre_admin_support_status_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function adminSupportStatusDeleteAction(Status $status)
    {
        if ($status->isLocked()) {
            throw new AccessDeniedException();
        }
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
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function adminSupportStatusReorderAction(Status $status, $nextStatusId)
    {
        $this->supportManager->reorderStatus($status, $nextStatusId);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/support/ticket/{ticket}/intervention/create/form",
     *     name="formalibre_admin_support_ticket_intervention_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportTicketInterventionModalForm.html.twig")
     */
    public function adminSupportTicketInterventionCreateFormAction(Ticket $ticket)
    {
        $form = $this->formFactory->create(new TicketInterventionType(), $ticket);

        return ['form' => $form->createView(), 'ticket' => $ticket];
    }

    /**
     * @EXT\Route(
     *     "/admin/support/ticket/{ticket}/intervention/create",
     *     name="formalibre_admin_support_ticket_intervention_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminSupportTicketInterventionModalForm.html.twig")
     */
    public function adminSupportTicketInterventionCreateAction(User $user, Ticket $ticket)
    {
        $oldType = $ticket->getType();
        $oldStatus = $ticket->getStatus();
        $form = $this->formFactory->create(new TicketInterventionType(), $ticket);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $data = [];
            $messageData = [];
            $status = $ticket->getStatus();
            $type = $ticket->getType();
            $privateComment = $form->get('privateComment')->getData();
            $publicComment = $form->get('publicComment')->getData();

            if ($status !== $oldStatus || $type !== $oldType) {
                if ($type !== $oldType) {
                    $data['type'] = [];
                    $data['type']['name'] = $ticket->getType()->getName();
                    $data['type']['description'] = $ticket->getType()->getDescription();
                    $messageData['oldType'] = $oldType;
                    $messageData['type'] = $type;
                }
                if ($status !== $oldStatus) {
                    $intervention = $this->supportManager->createIntervention($ticket, $status, $user);
                    $data['status'] = [];
                    $data['status']['name'] = $status->getName();
                    $data['status']['date'] = $intervention->getEndDate()->format('d/m/Y H:i');
                    $data['status']['description'] = $status->getDescription();
                    $messageData['oldStatus'] = $oldStatus;
                    $messageData['status'] = $status;
                }
            } else {
                $this->supportManager->persistTicket($ticket);
            }
            if ($messageData || $publicComment) {
                $comment = $this->supportManager
                    ->createInterventionComment($user, $ticket, $messageData, Comment::PUBLIC_COMMENT, $publicComment);
                $data['publicComment'] = [];
                $data['publicComment']['comment'] = [];
                $data['publicComment']['comment']['id'] = $comment->getId();
                $data['publicComment']['comment']['content'] = $comment->getContent();
                $data['publicComment']['comment']['type'] = $comment->getType();
                $data['publicComment']['comment']['creationDate'] = $comment->getCreationDate()->format('d/m/Y H:i');
                $data['publicComment']['user']['id'] = $user->getId();
                $data['publicComment']['user']['firstName'] = $user->getFirstName();
                $data['publicComment']['user']['lastName'] = $user->getLastName();
                $data['publicComment']['user']['picture'] = $user->getPicture();
            }
            if ($messageData || $privateComment) {
                $comment = $this->supportManager
                    ->createInterventionComment($user, $ticket, $messageData, Comment::PRIVATE_COMMENT, $privateComment);
                $data['privateComment'] = [];
                $data['privateComment']['comment'] = [];
                $data['privateComment']['comment']['id'] = $comment->getId();
                $data['privateComment']['comment']['content'] = $comment->getContent();
                $data['privateComment']['comment']['type'] = $comment->getType();
                $data['privateComment']['comment']['creationDate'] = $comment->getCreationDate()->format('d/m/Y H:i');
                $data['privateComment']['user']['id'] = $user->getId();
                $data['privateComment']['user']['firstName'] = $user->getFirstName();
                $data['privateComment']['user']['lastName'] = $user->getLastName();
                $data['privateComment']['user']['picture'] = $user->getPicture();
            }

            return new JsonResponse($data, 200);
        } else {
            return ['form' => $form->createView(), 'ticket' => $ticket];
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/support/official/support/management",
     *     name="formalibre_admin_support_official_support_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportOfficialSupportManagementAction()
    {
        $supportToken = $this->platformConfigHandler->hasParameter('support_token') ?
            $this->platformConfigHandler->getParameter('support_token') :
            null;
        $supportPlatformUrl = $this->platformConfigHandler->hasParameter('support_platform_url') ?
            $this->platformConfigHandler->getParameter('support_platform_url') :
            null;
        $contactsData = null;
        $supportData = null;
        $platformUrl = !empty($this->platformConfigHandler->getParameter('domain_name')) ?
            $this->platformConfigHandler->getParameter('domain_name') :
            $this->request->getHost();

        if (!empty($supportToken)) {
            $contactsUrl = 'https://api.claroline.cloud/cc/platform/contacts/'.$supportToken;
            $supportUrl = 'https://api.claroline.cloud/cc/support/'.$supportToken;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $contactsUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $contactsData = (array) json_decode(curl_exec($ch));
            curl_setopt($ch, CURLOPT_URL, $supportUrl);
            $supportData = (array) json_decode(curl_exec($ch));
            curl_close($ch);
        }

        return [
            'supportToken' => $supportToken,
            'supportPlatformUrl' => $supportPlatformUrl,
            'title' => 'official_support',
            'noOfficialSupportInfo' => true,
            'contactsData' => $contactsData,
            'supportData' => $supportData,
            'platformUrl' => $platformUrl,
        ];
    }

    /**
     * @EXT\Route(
     *     "/admin/support/token/register",
     *     name="formalibre_admin_support_token_register",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function adminSupportTokenRegisterAction()
    {
        $token = $this->request->request->get('token', false);
        $platformUrl = $this->request->request->get('platformUrl', false);

        if ($token) {
            $this->platformConfigHandler->setParameter('support_token', $token);
        }
        if ($platformUrl) {
            $this->platformConfigHandler->setParameter('support_platform_url', $platformUrl);
        }

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/support/official/support/info",
     *     name="formalibre_admin_support_official_support_info",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportOfficialSupportInfoAction()
    {
        $supportToken = $this->platformConfigHandler->hasParameter('support_token') ?
            $this->platformConfigHandler->getParameter('support_token') :
            null;

        return ['supportToken' => $supportToken];
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/create/form",
     *     name="formalibre_admin_ticket_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminTicketCreateModalForm.html.twig")
     */
    public function adminTicketCreateFormAction(User $user)
    {
        $this->checkOfficialSupportAccess();
        $ticket = new Ticket();
        $ticket->setContactMail($user->getEmail());
        $ticket->setContactPhone($user->getPhone());
        $form = $this->formFactory->create(new AdminTicketType(), $ticket);

        return ['form' => $form->createView(), 'user' => $user];
    }

    /**
     * @EXT\Route(
     *     "/admin/ticket/create",
     *     name="formalibre_admin_ticket_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminTicketCreateModalForm.html.twig")
     */
    public function adminTicketCreateAction(User $user)
    {
        $supportToken = $this->checkOfficialSupportAccess();
        $platformUrl = $this->platformConfigHandler->hasParameter('support_platform_url') ?
            $this->platformConfigHandler->getParameter('support_platform_url') :
            '';
        $ticket = new Ticket();
        $ticket->setContactMail($user->getEmail());
        $ticket->setContactPhone($user->getPhone());
        $form = $this->formFactory->create(new AdminTicketType(), $ticket);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $postDataString = '{
                "type":"'.$ticket->getType()->getName().'",
                "subject":"'.urlencode($ticket->getTitle()).'",
                "platformUrl":"'.$platformUrl.'"
            }';
            $url = 'https://api.claroline.cloud/cc/support/'.$supportToken.'/tickets';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataString);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $returnedData = (array) json_decode(curl_exec($ch));
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 201) {
                $this->supportManager->initializeForwardedTicket($ticket, $user, null, $returnedData['id']);
                $data = [];
                $data['id'] = $ticket->getId();
                $data['title'] = $ticket->getTitle();
                $data['creationDate'] = $ticket->getCreationDate()->format('d/m/Y H:i');
                $data['user'] = [];
                $data['user']['firstName'] = $user->getFirstName();
                $data['user']['lastName'] = $user->getLastName();
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
            } else {
                $data = is_array($returnedData) && count($returnedData) > 0 ?
                    $returnedData[0] :
                    $this->translator->trans('forwarding_has_failed', [], 'support');
            }

            return new JsonResponse($data, $httpCode);
        } else {
            return ['form' => $form->createView(), 'user' => $user];
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/forwarded/ticket/{ticket}/create/form",
     *     name="formalibre_admin_forwarded_ticket_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminForwardedTicketCreateModalForm.html.twig")
     */
    public function adminForwardedTicketCreateFormAction(User $user, Ticket $ticket)
    {
        $this->checkOfficialSupportAccess();
        $forwardedTicket = new Ticket();
        $forwardedTicket->setTitle($ticket->getTitle());
        $forwardedTicket->setContactMail($user->getEmail());
        $forwardedTicket->setContactPhone($user->getPhone());
        $forwardedTicket->setDescription($ticket->getDescription());
        $forwardedTicket->setType($ticket->getType());
        $form = $this->formFactory->create(new AdminTicketType(), $forwardedTicket);

        return ['form' => $form->createView(), 'ticket' => $ticket, 'user' => $user];
    }

    /**
     * @EXT\Route(
     *     "/admin/forwarded/ticket/{ticket}/create",
     *     name="formalibre_admin_forwarded_ticket_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:AdminSupport:adminForwardedTicketCreateModalForm.html.twig")
     */
    public function adminForwardedTicketCreateAction(User $user, Ticket $ticket)
    {
        $supportToken = $this->checkOfficialSupportAccess();
        $platformUrl = $this->platformConfigHandler->hasParameter('support_platform_url') ?
            $this->platformConfigHandler->getParameter('support_platform_url') :
            '';
        $forwardedTicket = new Ticket();
        $forwardedTicket->setTitle($ticket->getTitle());
        $forwardedTicket->setContactMail($user->getEmail());
        $forwardedTicket->setContactPhone($user->getPhone());
        $forwardedTicket->setDescription($ticket->getDescription());
        $forwardedTicket->setType($ticket->getType());
        $form = $this->formFactory->create(new AdminTicketType(), $forwardedTicket);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $postDataString = '{
                "type":"'.$forwardedTicket->getType()->getName().'",
                "subject":"'.urlencode($forwardedTicket->getTitle()).'",
                "platformUrl":"'.$platformUrl.'"
            }';
            $url = 'https://api.claroline.cloud/cc/support/'.$supportToken.'/tickets';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataString);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $returnedData = (array) json_decode(curl_exec($ch));
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 201) {
                $this->supportManager->initializeForwardedTicket($forwardedTicket, $user, $ticket, $returnedData['id']);
                $data = [
                    'forwardedId' => $forwardedTicket->getId(),
                    'id' => $ticket->getId(),
                    'status_name' => $ticket->getStatus() ? $ticket->getStatus()->getName() : '',
                    'status_description' => $ticket->getStatus() ? $ticket->getStatus()->getDescription() : '',
                ];
            } else {
                $data = is_array($returnedData) && count($returnedData) > 0 ?
                    $returnedData[0] :
                    $this->translator->trans('forwarding_has_failed', [], 'support');
            }

            return new JsonResponse($data, $httpCode);
        } else {
            return ['form' => $form->createView(), 'ticket' => $ticket, 'user' => $user];
        }
    }

    /**
     * @EXT\Route(
     *     "/admin/forwarded/ticket/{ticket}/remove",
     *     name="formalibre_admin_forwarded_ticket_remove",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function adminForwardedTicketRemoveAction(Ticket $ticket)
    {
        $ticketId = $ticket->getId();
        $this->supportManager->deleteTicket($ticket);

        return new JsonResponse($ticketId, 200);
    }

    private function checkOfficialSupportAccess()
    {
        $supportToken = $this->platformConfigHandler->hasParameter('support_token') ?
            $this->platformConfigHandler->getParameter('support_token') :
            null;

        if (empty($supportToken)) {
            throw new AccessDeniedException();
        }

        return $supportToken;
    }
}
