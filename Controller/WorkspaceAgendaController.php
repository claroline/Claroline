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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Claroline\AgendaBundle\Entity\Event;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\AgendaBundle\Manager\AgendaManager;
use Claroline\AgendaBundle\Form\ImportAgendaType;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use JMS\SecurityExtraBundle\Annotation as SEC;

/**
 * Controller of the agenda
 */
class WorkspaceAgendaController extends Controller
{
    private $om;
    private $request;
    private $agendaManager;
    private $router;
    private $authorization;

    /**
     * @DI\InjectParams({
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "request"            = @DI\Inject("request"),
     *     "agendaManager"      = @DI\Inject("claroline.manager.agenda_manager"),
     *     "router"             = @DI\Inject("router"),
     *     "authorization"      = @DI\Inject("security.authorization_checker")
     * })
     */
    public function __construct(
        ObjectManager $om,
        Request $request,
        AgendaManager $agendaManager,
        RouterInterface $router,
        AuthorizationCheckerInterface $authorization
    )
    {
        $this->om = $om;
        $this->request = $request;
        $this->agendaManager = $agendaManager;
        $this->router = $router;
        $this->authorization = $authorization;
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/show",
     *     name="claro_workspace_agenda_show",
     *     options = {"expose"=true}
     * )
     *
     * @param Workspace $workspace
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Workspace $workspace)
    {
        $this->agendaManager->checkOpenAccess($workspace);
        $data = $this->agendaManager->displayEvents($workspace);

        return new JsonResponse($data, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/import/modal/form",
     *     name="claro_workspace_agenda_import_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineAgendaBundle:Tool:importIcsModalForm.html.twig")
     *
     * @param Workspace $workspace
     * @return array
     */
    public function importEventsModalForm(Workspace $workspace)
    {
        $this->agendaManager->checkEditAccess($workspace);
        $form = $this->createForm(new ImportAgendaType());

        return array('form' => $form->createView(), 'workspace' => $workspace);
    }

    /**
     * @EXT\Route("/workspace/{workspace}/import", name="claro_workspace_agenda_import")
     * @EXT\Template("ClarolineAgendaBundle:Tool:importIcsModalForm.html.twig")
     *
     * @param Workspace $workspace
     * @return array
     */
    public function importsEventsIcsAction(Workspace $workspace)
    {
        $this->agendaManager->checkEditAccess($workspace);
        $form = $this->createForm(new ImportAgendaType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $events = $this->agendaManager->importEvents($form->get('file')->getData(), $workspace);

            return new JsonResponse($events, 200);
        }

        return array('form' => $form->createView(), 'workspace' => $workspace);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/add/event/form",
     *     name="claro_workspace_agenda_add_event_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineAgendaBundle:Agenda:addEventModalForm.html.twig")
     *
     * @param Workspace $workspace
     * @return array
     */

    public function addEventModalFormAction(Workspace $workspace)
    {
        $this->agendaManager->checkEditAccess($workspace);
        $formType = $this->get('claroline.form.agenda');
        $form = $this->createForm($formType, new Event());

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace,
            'action' => $this->router->generate(
                'claro_workspace_agenda_add_event', array('workspace' => $workspace->getId())
            )
        );
    }

    /**
     * @EXT\Route("/{workspace}/add", name="claro_workspace_agenda_add_event")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineAgendaBundle:Agenda:addEventModalForm.html.twig")
     *
     * @param Workspace $workspace
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addEventAction(Workspace $workspace)
    {
        $this->agendaManager->checkEditAccess($workspace);
        $formType = $this->get('claroline.form.agenda');
        $form = $this->createForm($formType, new Event());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $event = $form->getData();
            $data = $this->agendaManager->addEvent($event, $workspace);

            return new JsonResponse(array($data), 200);
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace,
            'action' => $this->router->generate(
                'claro_workspace_agenda_add_event', array('workspace' => $workspace->getId())
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/{event}/update/form",
     *     name="claro_workspace_agenda_update_event_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineAgendaBundle:Agenda:updateEventModalForm.html.twig")
     *
     * @return array
     */
    public function updateEventModalFormAction(Event $event)
    {
        $this->agendaManager->checkEditAccess($event->getWorkspace());
        $formType = $this->get('claroline.form.agenda');
        $form = $this->createForm($formType, $event);

        return array(
            'form' => $form->createView(),
            'action' => $this->router->generate(
                'claro_workspace_agenda_update', array('event' => $event->getId())
            ),
            'event' => $event
        );
    }

    /**
     * @EXT\Route(
     *     "/{event}/update",
     *     name="claro_workspace_agenda_update"
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineAgendaBundle:Agenda:updateEventModalForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Event $event)
    {
        $this->agendaManager->checkEditAccess($event->getWorkspace());
        $formType = $this->get('claroline.form.agenda');
        $form = $this->createForm($formType, $event);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $event = $this->agendaManager->updateEvent($event);

            return new JsonResponse($event, 200);
        }

        return array(
            'form' => $form->createView(),
            'action' => $this->router->generate(
                'claro_workspace_agenda_update', array('event' => $event->getId())
            ),
            'event' => $event
        );
    }
}