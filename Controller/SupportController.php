<?php

namespace FormaLibre\SupportBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use FormaLibre\SupportBundle\Entity\Ticket;
use FormaLibre\SupportBundle\Form\TicketType;
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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenTool('formalibre_support_tool')")
 */
class SupportController extends Controller
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
     *     "/support/index/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="formalibre_support_index",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="num","order"="DESC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function supportIndexAction(
        User $authenticatedUser,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'num',
        $order = 'DESC'
    )
    {
        $tickets = $this->supportManager->getTicketsByUser(
            $authenticatedUser,
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
            'lastStatus' => $lastStatus,
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "ticket/create/form",
     *     name="formalibre_ticket_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:Support:ticketCreateForm.html.twig")
     */
    public function ticketCreateFormAction(User $authenticatedUser)
    {
        $ticket = new Ticket();
        $ticket->setUser($authenticatedUser);
        $ticket->setContactMail($authenticatedUser->getMail());
        $phone = $authenticatedUser->getPhone();

        if (!is_null($phone)) {
            $ticket->setContactPhone($phone);
        }
        $form = $this->formFactory->create(new TicketType(), $ticket);

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "ticket/create",
     *     name="formalibre_ticket_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:Support:ticketCreateForm.html.twig")
     */
    public function ticketCreateAction(User $authenticatedUser)
    {
        $ticket = new Ticket();
        $ticket->setUser($authenticatedUser);
        $form = $this->formFactory->create(new TicketType(), $ticket);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $num = $this->supportManager->generateTicketNum($authenticatedUser);
            $ticket->setNum($num);
            $now = new \DateTime();
            $ticket->setCreationDate($now);
            $this->supportManager->persistTicket($ticket);

            return new RedirectResponse(
                $this->router->generate('formalibre_support_index')
            );
        } else {

            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "ticket/{ticket}/edit/form",
     *     name="formalibre_ticket_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:Support:ticketEditForm.html.twig")
     */
    public function ticketEditFormAction(User $authenticatedUser, Ticket $ticket)
    {
        $this->checkTicketEditionAccess($authenticatedUser, $ticket);
        $form = $this->formFactory->create(new TicketType(), $ticket);

        return array(
            'form' => $form->createView(),
            'ticket' => $ticket
        );
    }

    /**
     * @EXT\Route(
     *     "ticket/{ticket}/edit",
     *     name="formalibre_ticket_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibreSupportBundle:Support:ticketEditForm.html.twig")
     */
    public function ticketEditAction(User $authenticatedUser, Ticket $ticket)
    {
        $this->checkTicketEditionAccess($authenticatedUser, $ticket);
        $form = $this->formFactory->create(new TicketType(), $ticket);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->supportManager->persistTicket($ticket);

            return new RedirectResponse(
                $this->router->generate('formalibre_support_index')
            );
        } else {

            return array(
                'form' => $form->createView(),
                'ticket' => $ticket
            );
        }
    }

    /**
     * @EXT\Route(
     *     "ticket/{ticket}/delete",
     *     name="formalibre_ticket_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function ticketDeleteAction(User $authenticatedUser, Ticket $ticket)
    {
        $this->checkTicketEditionAccess($authenticatedUser, $ticket);
        $this->supportManager->deleteTicket($ticket);

        return new JsonResponse('success', 200);
    }

    private function checkTicketEditionAccess(User $user, Ticket $ticket)
    {
        $interventions = $ticket->getInterventions();

        if ($user->getId() !== $ticket->getUser()->getId() || count($interventions) > 0) {

            throw new AccessDeniedException();
        }
    }
}
