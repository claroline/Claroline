<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Event\DisplayToolEvent;
use Claroline\CoreBundle\Entity\Event;
use Claroline\CoreBundle\Form\CalendarType;

/**
 * @DI\Service
 */
class ToolListener
{
    private $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
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
        $httpKernel = $this->container->get('http_kernel');
        $response = $httpKernel->forward(
            'ClarolineCoreBundle:Tool\User:registeredUsersList',
            array('workspaceId' => $workspaceId, 'page' => 1, 'search' => '')
        );
        $event->setContent(($response->getContent()));
    }

    /**
     * @DI\Observe("open_tool_workspace_group_management")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceGroupManagement(DisplayToolEvent $event)
    {
        $workspaceId = $event->getWorkspace()->getId();
        $httpKernel = $this->container->get('http_kernel');
        $response = $httpKernel->forward(
            'ClarolineCoreBundle:Tool\Group:registeredGroupsList',
            array('workspaceId' => $workspaceId, 'page' => 1, 'search' => '')
        );
        $event->setContent(($response->getContent()));
    }

    /**
     * @DI\Observe("open_tool_workspace_calendar")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceCalendar(DisplayToolEvent $event)
    {
        $event->setContent($this->workspaceCalendar($event->getWorkspace()->getId()));
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
     * @DI\Observe("open_tool_desktop_logs")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopLogs(DisplayToolEvent $event)
    {
        $event->setContent($this->desktopLogs());
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
     * @DI\Observe("open_tool_desktop_calendar")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopCalendar(DisplayToolEvent $event)
    {
        $event->setContent($this->desktopCalendar());
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
        $em = $this->container->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:parameters.html.twig',
            array('workspace' => $workspace)
        );
    }

    /**
     * Displays the Info desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopParameters()
    {
        return $this->container
            ->get('templating')
            ->render('ClarolineCoreBundle:Tool\desktop\parameters:parameters.html.twig');
    }

    public function workspaceCalendar($workspaceId)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
        $form = $this->container->get('form.factory')->create(new CalendarType());
        $listEvents = $em->getRepository('ClarolineCoreBundle:Event')->findByWorkspaceId($workspaceId, true);

        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Tool/workspace/calendar:calendar.html.twig',
            array('workspace' => $workspace,
                'form' => $form->createView(),
                'listEvents' => $listEvents )
        );

    }

    public function workspaceLogs($workspaceId)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Tool/workspace/logs:log_list.html.twig',
            $this->container->get('claroline.log.manager')->getWorkspaceList($workspace, 1)
        );
    }

    public function desktopLogs()
    {
        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Tool/desktop/logs:log_list.html.twig',
            $this->container->get('claroline.log.manager')->getDesktopList(1)
        );
    }

    public function desktopCalendar()
    {
        $event = new Event();
        $formBuilder = $this->container->get('form.factory')->createBuilder(new CalendarType(), $event, array());
        $em = $this->container-> get('doctrine.orm.entity_manager');
        $listEvents = $em->getRepository('ClarolineCoreBundle:Event')->findAll();
        $cours = array();

        foreach ($listEvents as $event) {
            $cours[] = $event->getWorkspace()->getName();
        }

        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Tool/desktop/calendar:calendar.html.twig',
            array(
                'form' => $formBuilder-> getForm()-> createView(),
                'listEvents' => $listEvents,
                'cours' => array_unique($cours)
                )
        );
    }
}


