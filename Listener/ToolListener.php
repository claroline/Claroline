<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Entity\Event;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;

/**
 * @DI\Service()
 */
class ToolListener
{
    private $container;
    private $toolManager;
    private $workspaceManager;
    private $formFactory;
    private $templating;
    private $httpKernel;
    const R_U = "ROLE_USER";
    const R_A = "ROLE_ADMIN";

    /**
     * @DI\InjectParams({
     *     "container"        = @DI\Inject("service_container"),
     *     "toolManager"      = @DI\Inject("claroline.manager.tool_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "formFactory"      = @DI\Inject("claroline.form.factory"),
     *     "templating"       = @DI\Inject("templating"),
     *     "httpKernel"       = @DI\Inject("http_kernel")
     * })
     */
    public function __construct(
        ContainerInterface $container,
        ToolManager $toolManager,
        WorkspaceManager $workspaceManager,
        FormFactory $formFactory,
        $templating,
        $httpKernel
    )
    {
        $this->container = $container;
        $this->toolManager = $toolManager;
        $this->workspaceManager = $workspaceManager;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
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
     * @DI\Observe("open_tool_workspace_agenda")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceAgenda(DisplayToolEvent $event)
    {
        $event->setContent($this->workspaceAgenda($event->getWorkspace()));
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
     * @DI\Observe("open_tool_workspace_analytics")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceAnalytics(DisplayToolEvent $event)
    {
        $event->setContent($this->workspaceAnalytics($event->getWorkspace()));
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

        if (count($tools) > 1) {
            return $this->templating->render(
                'ClarolineCoreBundle:Tool\desktop\parameters:parameters.html.twig',
                array('tools' => $tools)
            );
        }

        //otherwise only parameters exists so we return the parameters page.
        $params['_controller'] = 'ClarolineCoreBundle:Tool\DesktopParameters:desktopConfigureTool';

        $subRequest = $this->container->get('request')->duplicate(
            array(),
            null,
            $params
        );
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $response->getContent();
    }

    public function workspaceAgenda(Workspace $workspace)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $listEvents = $em->getRepository('ClarolineCoreBundle:Event')
            ->findByWorkspaceId($workspace->getId(), true);
        $canCreate = $this->container->get('security.context')
            ->isGranted(array('agenda', 'edit'), $workspace);

        return $this->templating->render(
            'ClarolineCoreBundle:Tool/workspace/agenda:agenda.html.twig',
            array(
                'workspace' => $workspace,
                'canCreate' => $canCreate
            )
        );
    }

    public function workspaceLogs($workspaceId)
    {
        /** @var \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace */
        $workspace  = $this->workspaceManager->getWorkspaceById($workspaceId);

        return $this->templating->render(
            'ClarolineCoreBundle:Tool/workspace/logs:logList.html.twig',
            $this->container->get('claroline.log.manager')->getWorkspaceList($workspace, 1)
        );
    }

    public function workspaceAnalytics($workspace)
    {
        $params = array(
            '_controller' => 'ClarolineCoreBundle:WorkspaceAnalytics:showResources',
            'workspaceId' => $workspace->getId()
        );

        $subRequest = $this->container->get('request')->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $response->getContent();
    }

    public function desktopAgenda()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $usr = $this->container->get('security.context')->getToken()->getUser();
        $listEventsDesktop = $em->getRepository('ClarolineCoreBundle:Event')->findDesktop($usr, true);
        $listEvents = $em->getRepository('ClarolineCoreBundle:Event')->findByUser($usr, false);
        $workspaces = array();
        $filters = array();

        foreach ($listEvents as $event) {
            $filters[$event->getWorkspace()->getId()] = $event->getWorkspace()->getName();
        }

        if (count($listEventsDesktop) > 0) {
            $filters[0] = $this->container->get('translator')->trans('desktop', array(), 'platform');
        }

        return $this->templating->render(
            'ClarolineCoreBundle:Tool/desktop/agenda:agenda.html.twig',
            array(
                'listEvents' => $listEventsDesktop,
                'filters' => $filters
            )
        );
    }

    /**
     * @DI\Observe("open_tool_workspace_learning_profil")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceLearningProfil(DisplayToolEvent $event)
    {
        $params = array(
            '_controller' => 'ClarolineCoreBundle:Tool\CompetenceTool:listMyCompetences',
            'workspace' => $event->getWorkspace()->getId()
        );

        $subRequest = $this->container->get('request')->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setContent($response->getContent());
    }

    /**
     * @DI\Observe("open_tool_workspace_learning_outcomes")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceLearningOutcomes(DisplayToolEvent $event)
    {
        $params = array(
            '_controller' => 'ClarolineCoreBundle:Tool\CompetenceTool:workspaceLearningOutcomesList',
            'workspace' => $event->getWorkspace()->getId()
        );

        $subRequest = $this->container->get('request')->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setContent($response->getContent());
    }
}
