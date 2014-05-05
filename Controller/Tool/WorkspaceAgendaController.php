<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Entity\Event;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Manager\AgendaManager;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller of the agenda
 */
class WorkspaceAgendaController extends Controller
{
    private $security;
    private $formFactory;
    private $om;
    private $request;
    private $agendaManager;
    private $router;

    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "request"            = @DI\Inject("request"),
     *     "agendaManager"      = @DI\Inject("claroline.manager.agenda_manager"),
     *     "router"             = @DI\Inject("router"),
     *     "security"           = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        SecurityContextInterface $security,
        FormFactory $formFactory,
        ObjectManager $om,
        Request $request,
        AgendaManager $agendaManager,
        RouterInterface $router
    )
    {
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $request;
        $this->agendaManager = $agendaManager;
        $this->router = $router;
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/add",
     *     name="claro_workspace_agenda_add_event"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @param AbstractWorkspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addEventAction(AbstractWorkspace $workspace)
    { 
        $form = $this->formFactory->create(FormFactory::TYPE_AGENDA);
        $form->handleRequest($this->request);
        if ($form->isValid()) {
            $event = $form->getData();
            $json = $this->agendaManager->addEvent($event, $workspace);

            return new Response(
                json_encode($json['message']),
                $json['code'],
                array('Content-Type' => 'application/json')
            );
        }

        return new Response('Invalid data', 422);
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/update",
     *     name="claro_workspace_agenda_update"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @param AbstractWorkspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(AbstractWorkspace $workspace)
    {
        $postData = $this->request->request->all();
        $event = $this->om->getRepository('ClarolineCoreBundle:Event')->find($postData['id']);
        $form = $this->formFactory->create(FormFactory::TYPE_AGENDA, array(), $event);
        $form->handleRequest($this->request);
        if ($form->isValid()) {
            if($this->agendaManager->updateEvent($event, $postData['agenda_form']['allDay'], $workspace))
            {
                return new Response('', 204);
            }
        }

        return new Response(
            json_encode(
                array('dates are not valids')
            ),
            400,
            array('Content-Type' => 'application/json')
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/delete",
     *     name="claro_workspace_agenda_delete"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @param AbstractWorkspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(AbstractWorkspace $workspace)
    {
        $postData = $this->request->request->all();
        if ($this->agendaManager->deleteEvent($postData['id']))
        {    
            return new Response(
                json_encode(array('greeting' => 'delete')),
                200,
                array('Content-Type' => 'application/json')
            );
        }

        return new Response(
                json_encode(array('greeting' => 'fail')),
                400,
                array('Content-Type' => 'application/json')
            );
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/show",
     *     name="claro_workspace_agenda_show"
     * )
     * @EXT\Method({"GET","POST"})
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @param AbstractWorkspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(AbstractWorkspace $workspace)
    {
        $data = $this->agendaManager->displayEvents($workspace);

        return new Response(
            json_encode(
                $data
            ),
            200,
            array('Content-Type' => 'application/json')
        );
    }

    /**
     * @EXT\Route(
     *     "/move",
     *     name="claro_workspace_agenda_move"
     * )
     */
    public function moveAction()
    {
        $postData = $this->request->request->all();
        $data = $this->agendaManager->moveEvent($postData['id'], $postData['dayDelta'], $postData['minuteDelta']);
        
        return new Response(
            json_encode(
                $data  
            ),
            200,
            array('Content-Type' => 'application/json')
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/tasks",
     *     name="claro_workspace_agenda_tasks"
     * )
     * @EXT\Method({"GET","POST"})
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @param AbstractWorkspace $workspace
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\\desktop\\agenda:tasks.html.twig")
     */
    public function tasksAction(AbstractWorkspace $workspaceId)
    {
        $listEvents = $this->om->getRepository('ClarolineCoreBundle:Event')->findByWorkspaceId($workspaceId, true);

        return  array('listEvents' => $listEvents );
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/export",
     *     name="claro_workspace_agenda_export"
     * )
     * @EXT\Method({"GET","POST"})
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @param AbstractWorkspace $workspace
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportsEventIcsAction(AbstractWorkspace $workspaceId)
    {
        $file =  $this->agendaManager->export($workspaceId);
        $response = new StreamedResponse();

        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );
        $date = new \DateTime();
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$workspaceId->getName().'.ics');
        $response->headers->set('Content-Type', ' text/calendar');
        $response->headers->set('Connection', 'close');

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/import",
     *     name="claro_workspace_agenda_import"
     * )
     * @EXT\Method({"GET","POST"})
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @param AbstractWorkspace $workspace
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importsEventsIcsAction(AbstractWorkspace $workspace)
    {
        $form = $this->formFactory->create(FormFactory::TYPE_AGENDA_IMPORTER);
        $form->handleRequest($this->request);
        $listEvents = array();

        if ($form->isValid()) {
            $file = $form->get('file')->getData();
            $this->agendaManager->importsEvents($file, $workspace);
            return new RedirectResponse(
                $this->router->generate(
                    'claro_workspace_open_tool',
                    array('toolName'=>'agenda', 'workspaceId' => $workspace->getId()
                    )
                )
            );
        }
    }
}
