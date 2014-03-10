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
     *     "container" = @DI\Inject("service_container"),
     *     "toolManager" = @DI\Inject("claroline.manager.tool_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "formFactory" = @DI\Inject("claroline.form.factory"),
     *     "templating" = @DI\Inject("templating"),
     *     "httpKernel" = @DI\Inject("http_kernel")
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
        $file = $this->formFactory->create(FormFactory::TYPE_AGENDA_IMPORTER);
        $listEvents = $em->getRepository('ClarolineCoreBundle:Event')->findByWorkspaceId($workspaceId, true);
        $usr = $this->container->get('security.context')->getToken()->getUser();
        $owners = $em->getRepository('ClarolineCoreBundle:Event')->findByUserWithoutAllDay($usr, 0);
        $owner = array();
        foreach ($owners as $o) {
            $temp = $o->getUser()->getUserName();
            $owner[] = $temp;
        }
        $owners = array_unique($owner);

        if ($usr === 'anon.') {
            return $this->templating->render(
                'ClarolineCoreBundle:Tool/workspace/agenda:agenda_read_only.html.twig',
                array(
                    'workspace' => $workspace,
                    'form' => $form->createView(),
                    'listEvents' => $listEvents,
                    'owners' => $owners
                )
            );
        }

        return $this->templating->render(
            'ClarolineCoreBundle:Tool/workspace/agenda:agenda.html.twig',
            array(
                'workspace' => $workspace,
                'form' => $form->createView(),
                'file' => $file->createView(),
                'owners' => $owners
            )
        );

    }

    public function workspaceLogs($workspaceId)
    {
        /** @var \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace */
        $workspace  = $this->workspaceManager->getWorkspaceById($workspaceId);

        return $this->templating->render(
            'ClarolineCoreBundle:Tool/workspace/logs:logList.html.twig',
            $this->container->get('claroline.log.manager')->getWorkspaceList($workspace, 1)
        );
    }

    public function workspaceAnalytics($workspace)
    {
        return $this->templating->render(
            'ClarolineCoreBundle:Tool/workspace/analytics:analytics.html.twig',
            $this->container->get('claroline.manager.analytics_manager')->getWorkspaceAnalytics($workspace)
        );
    }

    public function desktopAgenda()
    {
        $event = new Event();
        $form = $this->formFactory->create(FormFactory::TYPE_AGENDA, array(), $event);
        $em = $this->container-> get('doctrine.orm.entity_manager');
        $usr = $this->container->get('security.context')->getToken()->getUser();
        $listEventsDesktop = $em->getRepository('ClarolineCoreBundle:Event')->findDesktop($usr, true);
        $listEvents = $em->getRepository('ClarolineCoreBundle:Event')->findByUser($usr, false);
        $cours = array();
        $translator = $this->container->get('translator');

        foreach ($listEvents as $event) {

            $temp = $event->getWorkspace()->getName();
            $cours[] = $temp;
        }
        if (count($listEventsDesktop) > 0) {

            $cours[] = $translator->trans('desktop', array(), 'platform');
        }

        return $this->templating->render(
            'ClarolineCoreBundle:Tool/desktop/agenda:agenda.html.twig',
            array(
                'form' => $form->createView(),
                'listEvents' => $listEventsDesktop,
                'cours' => array_unique($cours)
            )
        );
    }
}
