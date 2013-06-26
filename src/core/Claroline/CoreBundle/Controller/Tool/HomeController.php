<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Library\Event\ConfigureWidgetWorkspaceEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Event\ConfigureWidgetDesktopEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Controller of the workspace/desktop home page.
 */
class HomeController extends Controller
{
    /**
     * @Route(
     *     "/perso",
     *     name="claro_tool_desktop_perso"
     * )
     *
     * @Template("ClarolineCoreBundle:Tool\desktop\home:perso.html.twig")
     *
     * Displays the Perso desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function persoAction()
    {
        return array();
    }

    /**
     * @Route(
     *     "/info",
     *     name="claro_tool_desktop_info"
     * )
     *
     * @Template("ClarolineCoreBundle:Tool\desktop\home:info.html.twig")
     *
     * Displays the Info desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function infoAction()
    {
        return array();
    }

    /**
     * @Route(
     *     "workspace/{workspace}/widget",
     *     name="claro_workspace_widget_properties"
     * )
     * @Method("GET")
     *
     * @Template("ClarolineCoreBundle:Tool\workspace\home:widgetProperties.html.twig")
     *
     * Renders the workspace widget properties page.
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function workspaceWidgetsPropertiesAction(AbstractWorkspace $workspace)
    {
        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $configs = $this->get('claroline.widget.manager')
            ->generateWorkspaceDisplayConfig($workspace->getId());

        return array(
            'workspace' => $workspace,
            'configs' => $configs,
            'tool' => $this->getHomeTool()
        );
    }

    /**
     * @Route(
     *     "workspace/{workspace}/widget/{widget}/baseconfig/{adminConfig}/invertvisible",
     *     name="claro_workspace_widget_invertvisible",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     *
     * Inverts the visibility boolean of a widget in the specified workspace.
     * If the DisplayConfig entity for the workspace doesn't exist in the database
     * yet, it's created here.
     *
     * @param AbstractWorkspace workspace
     * @param Widget $widget
     * @param DisplayConfig $adminConfig The displayConfig defined by the administrator: it's the
     * configuration entity for widgets
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function workspaceInvertVisibleWidgetAction(
        AbstractWorkspace $workspace,
        Widget $widget,
        DisplayConfig $adminConfig
    )
    {
        $em = $this->getDoctrine()->getManager();

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $displayConfig = $em
            ->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findOneBy(array('workspace' => $workspace, 'widget' => $widget));

        if ($displayConfig === null) {
            $displayConfig = new DisplayConfig();
            $displayConfig->setParent($adminConfig);
            $displayConfig->setWidget($widget);
            $displayConfig->setWorkspace($workspace);
            $displayConfig->setVisible($adminConfig->isVisible());
            $displayConfig->setLock(true);
            $displayConfig->setDesktop(false);
        }

        $displayConfig->invertVisible();
        $em->persist($displayConfig);
        $em->flush();

        return new Response('success');
    }

    /**
     * @Route(
     *     "/{workspace}/widget/{widget}/configuration",
     *     name="claro_workspace_widget_configuration",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Asks a widget to render its configuration page for a workspace.
     *
     * @param AbstractWorkspace $workspace
     * @param Widget $widget
     *
     * @return Response
     */
    public function workspaceConfigureWidgetAction(AbstractWorkspace $workspace, Widget $widget)
    {
        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $event = new ConfigureWidgetWorkspaceEvent($workspace);
        $eventName = "widget_{$widget->getName()}_configuration_workspace";
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        if ($event->getContent() !== '') {
            if ($this->get('request')->isXMLHttpRequest()) {
                return $this->render(
                    'ClarolineCoreBundle:Tool\workspace\home:widgetConfigurationForm.html.twig',
                    array('content' => $event->getContent(), 'workspace' => $workspace, 'tool' => $this->getHomeTool())
                );
            }

            return $this->render(
                'ClarolineCoreBundle:Tool\workspace\home:widgetConfiguration.html.twig',
                array('content' => $event->getContent(), 'workspace' => $workspace, 'tool' => $this->getHomeTool())
            );
        }

        throw new \Exception("event {$eventName} didn't return any Response");
    }

    /**
     * @Route(
     *     "desktop/widget/properties",
     *     name="claro_desktop_widget_properties"
     * )
     *
     * @Template("ClarolineCoreBundle:Tool\desktop\home:widgetProperties.html.twig")
     *
     * Displays the widget configuration page.
     *
     * @return Response
     */
    public function desktopWidgetPropertiesAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $configs = $this->get('claroline.widget.manager')
            ->generateDesktopDisplayConfig($user->getId());

        return array(
            'configs' => $configs,
            'user' => $user,
            'tool' => $this->getHomeTool()
        );
    }

    /**
     * @Route(
     *     "desktop/config/{adminConfig}/widget/{widget}/invertvisible",
     *     name="claro_desktop_widget_invertvisible",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     *
     * Inverts the visibility boolean for a widget for the current user.
     *
     * @param Widget $widget the widget
     * @param DisplayConfig $adminConfig the display config (the configuration entity for widgets)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopInvertVisibleUserWidgetAction(Widget $widget, DisplayConfig $adminConfig)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $displayConfig = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findOneBy(array('user' => $user, 'widget' => $widget));

        if ($displayConfig === null) {
            $displayConfig = new DisplayConfig();
            $displayConfig->setParent($adminConfig);
            $displayConfig->setWidget($widget);
            $displayConfig->setUser($user);
            $displayConfig->setVisible($adminConfig->isVisible());
            $displayConfig->setLock(true);
            $displayConfig->setDesktop(true);
        }

        $displayConfig->invertVisible();
        $em->persist($displayConfig);
        $em->flush();

        return new Response('success');
    }

    /**
     * @Route(
     *     "desktop/widget/{widget}/configuration/desktop",
     *     name="claro_desktop_widget_configuration",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Asks a widget to display its configuration page.
     *
     * @param Widget $widget the widget
     *
     * @return Response
     */
    public function desktopConfigureWidgetAction(Widget $widget)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $event = new ConfigureWidgetDesktopEvent($user);
        $eventName = "widget_{$widget->getName()}_configuration_desktop";
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        if ($event->getContent() !== '') {
            if ($this->get('request')->isXMLHttpRequest()) {
                return $this->render(
                    'ClarolineCoreBundle:Tool\desktop\home:widgetConfigurationForm.html.twig',
                    array('content' => $event->getContent(), 'tool' => $this->getHomeTool())
                );
            }

            return $this->render(
                'ClarolineCoreBundle:Tool\desktop\home:widgetConfiguration.html.twig',
                array('content' => $event->getContent(), 'tool' => $this->getHomeTool())
            );
        }

        throw new \Exception("event $eventName didn't return any Response");
    }

    private function getHomeTool()
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneByName('home');
    }
}