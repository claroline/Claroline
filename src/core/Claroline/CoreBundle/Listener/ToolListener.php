<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Entity\Event;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;

/**
 * @DI\Service(scope="request")
 */
class ToolListener
{
    private $container;
    private $toolManager;
    private $workspaceManager;
    private $formFactory;
    private $templating;
    private $request;
    private $httpKernel;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     *     "toolManager" = @DI\Inject("claroline.manager.tool_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "formFactory" = @DI\Inject("claroline.form.factory"),
     *     "templating" = @DI\Inject("templating"),
     *     "request" = @DI\Inject("request"),
     *     "httpKernel" = @DI\Inject("http_kernel")
     * })
     */
    public function __construct(
        ContainerInterface $container,
        ToolManager $toolManager,
        WorkspaceManager $workspaceManager,
        FormFactory $formFactory,
        $templating,
        $request,
        $httpKernel
    )
    {
        $this->container = $container;
        $this->toolManager = $toolManager;
        $this->workspaceManager = $workspaceManager;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->request = $request;
        $this->httpKernel = $httpKernel;
    }

    /**
     * @DI\Observe("open_tool_workspace_parameters")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceParameters(DisplayToolEvent $event)
    {
         $event->setContent($this->workspaceParameters($event->getWorkspace()->getId()));
    }

    /**
     * @DI\Observe("open_tool_workspace_user_management")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceUserManagement(DisplayToolEvent $event)
    {
        $workspaceId = $event->getWorkspace()->getId();
        $response = $this->forward(
            'ClarolineCoreBundle:Tool\User:registeredUsersList',
            array('workspaceId' => $workspaceId, 'page' => 1, 'search' => '')
        );
        $event->setContent($response->getContent());
    }

    /**
     * @DI\Observe("open_tool_workspace_group_management")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceGroupManagement(DisplayToolEvent $event)
    {
        $workspaceId = $event->getWorkspace()->getId();
        $response = $this->forward(
            'ClarolineCoreBundle:Tool\Group:registeredGroupsList',
            array('workspaceId' => $workspaceId, 'page' => 1, 'search' => '')
        );
        $event->setContent($response->getContent());
    }

    /**
     * @DI\Observe("open_tool_workspace_agenda")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceAgenda(DisplayToolEvent $event)
    {
        $event->setContent($this->workspaceAgenda($event->getWorkspace()->getId()));
    }

    /**
     * @DI\Observe("open_tool_workspace_logs")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceLogs(DisplayToolEvent $event)
    {
        $event->setContent($this->workspaceLogs($event->getWorkspace()->getId()));
    }

    /**
     * @DI\Observe("open_tool_desktop_parameters")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopParameters(DisplayToolEvent $event)
    {
        $event->setContent($this->desktopParameters());
    }

    /**
     * @DI\Observe("open_tool_desktop_agenda")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopAgenda(DisplayToolEvent $event)
    {
        $event->setContent($this->desktopAgenda());
    }

    /**
     * @DI\Observe("open_tool_workspace_workgroup")
     *
     * @param \Claroline\CoreBundle\Event\DisplayToolEvent $event
     */
    public function onDisplayWorkgroup(DisplayToolEvent $event)
    {
        $event->setContent($this->workgroup($event->getWorkspace()->getId()));
    }

    /**
     * Renders the workspace properties page.
     *
     * @param integer $workspaceId
     *
     * @return string
     */
    public function workspaceParameters($workspaceId)
    {
        $workspace = $this->workspaceManager->getWorkspaceById($workspaceId);
        $tools = $this->toolManager->getToolByCriterias(
            array('isConfigurableInWorkspace' => true, 'isDisplayableInWorkspace' => true)
        );

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:parameters.html.twig',
            array('workspace' => $workspace, 'tools' => $tools)
        );
    }

    /**
     * Displays the Info desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopParameters()
    {
        $tools = $this->toolManager->getToolByCriterias(
            array('isConfigurableInDesktop' => true, 'isDisplayableInDesktop' => true)
        );

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\desktop\parameters:parameters.html.twig',
            array('tools' => $tools)
        );
    }

    public function workspaceAgenda($workspaceId)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $workspace = $this->workspaceManager->getWorkspaceById($workspaceId);
        $form = $this->formFactory->create(FormFactory::TYPE_AGENDA);
        $listEvents = $em->getRepository('ClarolineCoreBundle:Event')->findByWorkspaceId($workspaceId, true);

        return $this->templating->render(
            'ClarolineCoreBundle:Tool/workspace/agenda:agenda.html.twig',
            array('workspace' => $workspace,
                'form' => $form->createView(),
                'listEvents' => $listEvents )
        );

    }

    public function workspaceLogs($workspaceId)
    {
        $workspace = $this->workspaceManager->getWorkspaceById($workspaceId);

        return $this->templating->render(
            'ClarolineCoreBundle:Tool/workspace/logs:logList.html.twig',
            $this->container->get('claroline.log.manager')->getWorkspaceList($workspace, 1)
        );
    }

    public function desktopAgenda()
    {
        $event = new Event();
        $form = $this->formFactory->create(FormFactory::TYPE_AGENDA, array(), $event);
        $em = $this->container-> get('doctrine.orm.entity_manager');
        $listEvents = $em->getRepository('ClarolineCoreBundle:Event')->findAll();
        $cours = array();

        foreach ($listEvents as $event) {
            
            if (is_null($event->getWorkspace())){
                $temp = '';
            } else {
                $temp = $event->getWorkspace()->getName();
            }
            $cours[] = $temp;
        }

        return $this->templating->render(
            'ClarolineCoreBundle:Tool/desktop/agenda:agenda.html.twig',
            array(
                'form' => $form->createView(),
                'listEvents' => $listEvents,
                'cours' => array_unique($cours)
            )
        );
    }

    public function workgroup($workspaceId)
    {
        $workspace = $this->workspaceManager->getWorkspaceById($workspaceId);

        return $this->templating->render(
            'ClarolineCoreBundle:Tool/workspace/workgroup:workgroup.html.twig',
            array('workspace' => $workspace)
        );
    }

    private function forward($controller, array $parameters = array())
    {
        $parameters['_controller'] = $controller;
        $subRequest = $this->request->duplicate(array(), null, $parameters);

        return $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
