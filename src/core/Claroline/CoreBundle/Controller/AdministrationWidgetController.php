<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class AdministrationWidgetController extends Controller
{
    /**
     * @EXT\Route(
     *     "/widgets",
     *     name="claro_admin_widgets"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:widgets.html.twig")
     *
     * Displays the list of widget options for the administrator.
     *
     * @return Response
     */
    public function widgetListAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $wconfigs = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findBy(array('parent' => null, 'isDesktop' => false));
        $dconfigs = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findBy(array('parent' => null, 'isDesktop' => true));

        return array(
            'wconfigs' => $wconfigs,
            'dconfigs' => $dconfigs
        );
    }

    /**
     * @EXT\Route(
     *     "/plugin/lock/{displayConfigId}",
     *     name="claro_admin_invert_widgetconfig_lock",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "displayConfig",
     *      class="ClarolineCoreBundle:Widget\DisplayConfig",
     *      options={"id" = "displayConfigId", "strictId" = true}
     * )
     *
     * Sets true|false to the widget displayConfig isLockedByAdmin option.
     *
     * @param DisplayConfig $displayConfig
     *
     * @return Response
     */
    public function invertLockWidgetAction(DisplayConfig $displayConfig)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $displayConfig->invertLock();
        $em->persist($displayConfig);
        $em->flush();

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "widget/{widgetId}/configuration/workspace",
     *     name="claro_admin_widget_configuration_workspace",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "widget",
     *      class="ClarolineCoreBundle:Widget\Widget",
     *      options={"id" = "widgetId", "strictId" = true}
     * )
     *
     * Asks a widget to render its configuration form for a workspace.
     *
     * @param Widget $widget
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:widgetConfiguration.html.twig")
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function configureWorkspaceWidgetAction(Widget $widget)
    {
        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            "widget_{$widget->getName()}_configuration_workspace",
            'ConfigureWidgetWorkspace',
            array(null, true)
        );

        return array('content' => $event->getContent());
    }

    /**
     * @EXT\Route(
     *     "widget/{widgetId}/configuration/desktop",
     *     name="claro_admin_widget_configuration_desktop",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "widget",
     *      class="ClarolineCoreBundle:Widget\Widget",
     *      options={"id" = "widgetId", "strictId" = true}
     * )
     *
     * Asks a widget to render its configuration form for a workspace.
     *
     * @param Widget $widget
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:widgetConfiguration.html.twig")
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function configureDesktopWidgetAction(Widget $widget)
    {
        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            "widget_{$widget->getName()}_configuration_desktop",
            "ConfigureWidgetDesktop",
            array(null, true)
        );

        return array('content' => $event->getContent());
    }

    /**
     * @EXT\Route(
     *     "/plugin/visible/{displayConfigId}",
     *     name="claro_admin_invert_widgetconfig_visible",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "displayConfig",
     *      class="ClarolineCoreBundle:Widget\DisplayConfig",
     *      options={"id" = "displayConfigId", "strictId" = true}
     * )
     *
     * Sets true|false to the widget displayConfig isVisible option.
     *
     * @param DisplayConfig $displayConfig
     */
    public function invertVisibleWidgetAction(DisplayConfig $displayConfig)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $displayConfig->invertVisible();
        $em->persist($displayConfig);
        $em->flush();

        return new Response('success', 204);
    }
}