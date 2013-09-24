<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\SecurityExtraBundle\Annotation as SEC;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ADMIN')")
 */
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
            ->findBy(array('isAdmin' => true, 'isDesktop' => false));
        $dconfigs = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findBy(array('isAdmin' => true, 'isDesktop' => true));
        
        $widgets = $em->getRepository('ClarolineCoreBundle:Widget\Widget')->findAll();
        
        return array(
            'widgets' => $widgets,
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
        $em = $this->getDoctrine()->getManager();
        $displayConfig->invertLock();
        $em->persist($displayConfig);
        $em->flush();

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "widget/{config}/configuration/workspace",
     *     name="claro_admin_widget_configuration",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
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
    public function configureWidgetAction(DisplayConfig $config)
    {
        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            "widget_{$config->getWidget()->getName()}_configuration",
            'ConfigureWidget',
            array($config)
        );

        return array('content' => $event->getContent());
    }
    
    /**
     * @EXT\Route(
     *     "/widget/name/form/{config}",
     *     name = "claro_admin_widget_name_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:editWidgetNameForm.html.twig")
     * 
     * @param \Claroline\CoreBundle\Entity\Widget\DisplayConfig $config
     * 
     * @return array
     */
    public function editWidgetNameFormAction(DisplayConfig $config)
    {   
        $formFactory = $this->get("claroline.form.factory");
        $form = $formFactory->create(FormFactory::TYPE_WIDGET_CONFIG, array(), $config);
        
        return array('form' => $form->createView(), 'config' => $config);
    }

    /**
     * @EXT\Route(
     *     "/widget/name/edit/{config}",
     *     name = "claro_admin_widget_name_edit",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:editWidgetNameForm.html.twig")
     * 
     * @param \Claroline\CoreBundle\Entity\Widget\DisplayConfig $config
     * 
     * @return array
     */
    public function editWidgetName(DisplayConfig $config)
    {
        $formFactory = $this->get("claroline.form.factory");
        $form = $formFactory->create(FormFactory::TYPE_WIDGET_CONFIG, array(), $config);
        $form->handleRequest($this->get('request'));
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $config = $form->getData();
            $em->persist($config);
            $em->flush();
            
            return new Response('success', 204);
        } else {
            return array('form' => $form->createView(), 'config' => $config);
        }
    }
    
    /**
     * @EXT\Route(
     *     "/workspace/widget/{widget}/create",
     *     name = "claro_admin_create_workspace_widget",
     *     options={"expose"=true}
     * )
     */
    public function createWorkspaceWidgetInstance(Widget $widget)
    {
        $em = $this->getDoctrine()->getManager();
        $config = new DisplayConfig($widget);
        $config->setName($widget->getName());
        $config->setIsAdmin(true);
        $config->setIsDesktop(false);
        $config->setWidget($widget);
        $em->persist($config);
        $em->flush();
        
        return new Response('success');
    }
    
    /**
     * @EXT\Route(
     *     "/desktop/widget/{widget}/create",
     *     name = "claro_admin_create_desktop_widget",
     *     options={"expose"=true}
     * )
     */
    public function createDesktopWidgetInstance(Widget $widget)
    {
        $em = $this->getDoctrine()->getManager();
        $config = new DisplayConfig($widget);
        $config->setName($widget->getName());
        $config->setIsAdmin(true);
        $config->setIsDesktop(true);
        $config->setWidget($widget);
        $em->persist($config);
        $em->flush();
        
        return new Response('success');
    }
}
   
