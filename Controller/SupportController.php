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
     *     "/support/index/ordered/by/{orderedBy}/order/{order}",
     *     name="formalibre_support_index",
     *     defaults={"orderedBy"="num","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function supportIndexAction(
        User $authenticatedUser,
        $orderedBy = 'num',
        $order = 'ASC'
    )
    {
        $tickets = $this->supportManager->getTicketsByUser(
            $authenticatedUser,
            $orderedBy,
            $order
        );

        return array(
            'tickets' => $tickets,
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
    public function ticketCreateFormAction()
    {
        $form = $this->formFactory->create(new TicketType(), new Ticket());

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
            $ticket->setStatus(0);
            $ticket->setSpentTime(0);
            $now = new \DateTime();
            $ticket->setCreationDate($now);
            $ticket->setStatusDate($now);
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
    public function ticketEditFormAction(Ticket $ticket)
    {
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
    public function ticketEditAction(Ticket $ticket)
    {
        $form = $this->formFactory->create(new TicketType(), $ticket);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $now = new \DateTime();
            $ticket->setStatusDate($now);
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
        $this->checkTicketAccess($authenticatedUser, $ticket);
        $this->supportManager->deleteTicket($ticket);

        return new JsonResponse('success', 200);
    }

    private function checkTicketAccess(User $user, Ticket $ticket)
    {
        if ($user->getId() !== $ticket->getUser()->getId()) {

            throw new AccessDeniedException();
        }
    }
}
