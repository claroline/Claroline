<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\WidgetManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\SecurityExtraBundle\Annotation as SEC;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ADMIN')")
 */
class AdministrationWidgetController extends Controller
{
    private $widgetManager;

    /**
     * @DI\InjectParams({
     *     "widgetManager" = @DI\Inject("claroline.manager.widget_manager")
     * })
     */
    public function __construct(WidgetManager $widgetManager)
    {
        $this->widgetManager = $widgetManager;
    }

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
        $wconfigs = $em->getRepository('ClarolineCoreBundle:Widget\WidgetInstance')
            ->findBy(array('isAdmin' => true, 'isDesktop' => false));
        $dconfigs = $em->getRepository('ClarolineCoreBundle:Widget\WidgetInstance')
            ->findBy(array('isAdmin' => true, 'isDesktop' => true));

        $dwidgets = $this->widgetManager->getDesktopWidgets();
        $wwidgets = $this->widgetManager->getWorkspaceWidgets();

        return array(
            'dwidgets' => $dwidgets,
            'wwidgets' => $wwidgets,
            'wconfigs' => $wconfigs,
            'dconfigs' => $dconfigs
        );
    }

    /**
     * @EXT\Route(
     *     "widget/{config}/configuration/workspace",
     *     name="claro_admin_widget_configuration",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "widgetInstance",
     *     class="ClarolineCoreBundle:Widget\WidgetInstance",
     *     options={"id" = "widgetInstanceId", "strictId" = true}
     * )
     * @EXT\Method("GET")
     *
     * Asks a widget to render its configuration form for a workspace.
     *
     * @param WidgetInstance $widgetInstance
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:widgetConfiguration.html.twig")
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function configureWidgetAction(WidgetInstance $widgetInstance)
    {
        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            "widget_{$widgetInstance->getWidget()->getName()}_configuration",
            'ConfigureWidget',
            array($widgetInstance)
        );

        return array('content' => $event->getContent());
    }

    /**
     * @EXT\Route(
     *     "/widget/name/form/{widgetInstanceId}",
     *     name = "claro_admin_widget_name_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "widgetInstance",
     *     class="ClarolineCoreBundle:Widget\WidgetInstance",
     *     options={"id" = "widgetInstanceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:editWidgetNameForm.html.twig")
     *
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetInstance $widgetInstance
     *
     * @return array
     */
    public function editWidgetNameFormAction(WidgetInstance $widgetInstance)
    {
        $formFactory = $this->get("claroline.form.factory");
        $form = $formFactory->create(
            FormFactory::TYPE_WIDGET_CONFIG,
            array(),
            $widgetInstance
        );

        return array('form' => $form->createView(), 'widgetInstance' => $widgetInstance);
    }

    /**
     * @EXT\Route(
     *     "/widget/name/edit/{widgetInstanceId}",
     *     name = "claro_admin_widget_name_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "widgetInstance",
     *     class="ClarolineCoreBundle:Widget\WidgetInstance",
     *     options={"id" = "widgetInstanceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:editWidgetNameForm.html.twig")
     *
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetInstance $widgetInstance
     *
     * @return array
     */
    public function editWidgetName(WidgetInstance $widgetInstance)
    {
        $formFactory = $this->get("claroline.form.factory");
        $form = $formFactory->create(
            FormFactory::TYPE_WIDGET_CONFIG,
            array(),
            $widgetInstance
        );
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $widgetInstance = $form->getData();
            $em->persist($widgetInstance);
            $em->flush();

            return new Response('success', 204);
        } else {

            return array(
                'form' => $form->createView(),
                'widgetInstance' => $widgetInstance
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/workspace/widget/{widget}/create",
     *     name = "claro_admin_create_workspace_widget",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Widget:adminWidgetConfigRow.html.twig")
     */
    public function createWorkspaceWidgetInstance(Widget $widget)
    {
       $instance = $this->widgetManager->createInstance($widget, true, false);

        return array('config' => $instance);
    }

    /**
     * @EXT\Route(
     *     "/desktop/widget/{widget}/create",
     *     name = "claro_admin_create_desktop_widget",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Widget:adminWidgetConfigRow.html.twig")
     */
    public function createDesktopWidgetInstance(Widget $widget)
    {
        $instance = $this->widgetManager->createInstance($widget, true, true);

        return array('config' => $instance);
    }

    /**
     * @EXT\Route(
     *     "/widget/remove/{widgetInstance}",
     *     name = "claro_admin_remove_widget",
     *     options={"expose"=true}
     * )
     */
    public function removeWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetManager->removeInstance($widgetInstance);

        return new Response(204);
    }
}

