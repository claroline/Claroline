<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Controller;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AgendaBundle\Entity\EventInvitation;
use Claroline\AgendaBundle\Form\EventInvitationType;
use Claroline\AgendaBundle\Form\ImportAgendaType;
use Claroline\AgendaBundle\Manager\AgendaManager;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ROLE_USER')")
 */
class DesktopAgendaController extends Controller
{
    private $tokenStorage;
    private $om;
    private $request;
    private $translator;
    private $agendaManager;
    private $router;
    private $eventDispatcher;

    /**
     * @DI\InjectParams({
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "request"         = @DI\Inject("request"),
     *     "translator"      = @DI\Inject("translator"),
     *     "agendaManager"   = @DI\Inject("claroline.manager.agenda_manager"),
     *     "router"          = @DI\Inject("router"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        Request $request,
        TranslatorInterface $translator,
        AgendaManager $agendaManager,
        RouterInterface $router,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->request = $request;
        $this->translator = $translator;
        $this->agendaManager = $agendaManager;
        $this->router = $router;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @EXT\Route(
     *     "/show",
     *     name="claro_desktop_agenda_show",
     *     options={"expose"=true}
     * )
     */
    public function desktopShowAction()
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $events = $this->agendaManager->desktopEvents($user);
        $options = [
            'type' => 'desktop',
            'user' => $user,
        ];
        $genericEvent = $this->eventDispatcher->dispatch('claroline_external_agenda_events', new GenericDataEvent($options));
        $externalEvents = $genericEvent->getResponse();
        $data = array_merge($events, $externalEvents);

        return new JsonResponse($data);
    }

    /**
     * @EXT\Route(
     *     "/add/event/form",
     *     name="claro_desktop_agenda_add_event_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineAgendaBundle:Agenda:addEventModalForm.html.twig")
     *
     * @return array
     */
    public function addEventModalFormAction()
    {
        $formType = $this->get('claroline.form.agenda');
        $formType->setIsDesktop();
        $form = $this->createForm($formType, new Event());

        return [
            'form' => $form->createView(),
            'action' => $this->router->generate('claro_desktop_agenda_add'),
        ];
    }

    /**
     * @EXT\Route("/add", name="claro_desktop_agenda_add")
     * @EXT\Template("ClarolineAgendaBundle:Agenda:addEventModalForm.html.twig")
     */
    public function addEvent()
    {
        $formType = $this->get('claroline.form.agenda');
        $formType->setIsDesktop();
        $form = $this->createForm($formType, new Event());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $event = $form->getData();
            $users = $form->get('users')->getData();
            $data = $this->agendaManager->addEvent($event,  $event->getWorkspace(), $users);

            return new JsonResponse([$data], 200);
        }

        return [
            'form' => $form->createView(),
            'action' => $this->router->generate('claro_desktop_agenda_add', []),
        ];
    }

    /**
     * @EXT\Route(
     *     "/{event}/update/form",
     *     name="claro_desktop_agenda_update_event_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineAgendaBundle:Agenda:updateEventModalForm.html.twig")
     *
     * @return array
     */
    public function updateEventModalFormAction(Event $event)
    {
        $formType = $this->get('claroline.form.agenda');
        $formType->setIsDesktop();
        $form = $this->createForm($formType, $event);

        return [
            'form' => $form->createView(),
            'action' => $this->router->generate(
                'claro_desktop_agenda_update', ['event' => $event->getId()]
            ),
            'event' => $event,
        ];
    }

    /**
     * @EXT\Route(
     *     "/{event}/update",
     *     name="claro_desktop_agenda_update"
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineAgendaBundle:Agenda:updateEventModalForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Event $event)
    {
        $formType = $this->get('claroline.form.agenda');
        $formType->setIsDesktop();
        $form = $this->createForm($formType, $event);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            if ($event->getWorkspace()) {
                $this->agendaManager->checkEditAccess($event->getWorkspace());
            }

            $event = $this->agendaManager->updateEvent($event, $form->get('users')->getData());

            return new JsonResponse($event, 200);
        }

        return [
            'form' => $form->createView(),
            'action' => $this->router->generate(
                'claro_desktop_agenda_update', ['event' => $event->getId()]
            ),
            'event' => $event,
        ];
    }

    /**
     * @EXT\Route(
     *     "/{event}/guest/update",
     *     name="claro_desktop_agenda_guest_update",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineAgendaBundle:Agenda:updateEventModalForm.html.twig")
     */
    public function guestUpdateAction(Event $event)
    {
        $invitation = $this->checkGuestAccess($event);

        $form = $this->createForm(new EventInvitationType($this->translator), $invitation);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->om->flush();

            return new JsonResponse($event->jsonSerialize($this->tokenStorage->getToken()->getUser()));
        }

        return [
            'form' => $form->createView(),
            'action' => $this->router->generate(
                'claro_desktop_agenda_guest_update', ['event' => $event->getId()]
            ),
            'event' => $event,
            'isGuest' => true,
        ];
    }

    /**
     * @EXT\Route(
     *     "/{event}/delete",
     *     name="claro_agenda_delete_guest_event",
     *     options={"expose"=true}
     * )
     *
     * @param Event $event
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function guestDeleteAction(Event $event)
    {
        $invitation = $this->checkGuestAccess($event);

        $invitation->setStatus(EventInvitation::RESIGN);
        $this->om->flush();

        return new JsonResponse($event->jsonSerialize(), 200);
    }

    /**
     * @EXT\Route("/widget/{order}", name="claro_desktop_agenda_widget")
     * @EXT\Template("ClarolineAgendaBundle:Widget:agenda_widget.html.twig")
     */
    public function widgetAction($order = null)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $usr = $this->tokenStorage->getToken()->getUser();
        $listEventsDesktop = $em->getRepository('ClarolineAgendaBundle:Event')->findDesktop($usr, false);
        $listEvents = $em->getRepository('ClarolineAgendaBundle:Event')->findByUserWithoutAllDay($usr, 5, $order);

        return ['listEvents' => array_merge($listEvents, $listEventsDesktop)];
    }

    /**
     * @EXT\Route(
     *     "/import/modal/form",
     *     name="claro_agenda_import_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineAgendaBundle:Tool:importIcsModalForm.html.twig")
     *
     * @return array
     */
    public function importEventsModalForm()
    {
        $form = $this->createForm(new ImportAgendaType());

        return ['form' => $form->createView()];
    }

    /**
     * @EXT\Route("/import", name="claro_agenda_import")
     * @EXT\Template("ClarolineAgendaBundle:Tool:importIcsModalForm.html.twig")
     *
     * @return array
     */
    public function importsEventsIcsAction()
    {
        $form = $this->createForm(new ImportAgendaType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $events = $this->agendaManager->importEvents($form->get('file')->getData());

            return new JsonResponse($events, 200);
        }

        return ['form' => $form->createView()];
    }

    public function checkGuestAccess(Event $event)
    {
        $eventInvitation = $this->om->getRepository('ClarolineAgendaBundle:EventInvitation')->findOneBy([
            'event' => $event,
            'user' => $this->tokenStorage->getToken()->getUser(),
        ]);

        if (!$eventInvitation) {
            throw new AccessDeniedException('You cannot change this invitation.');
        }

        return $eventInvitation;
    }
}
