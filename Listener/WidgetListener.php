<?php

namespace Icap\BlogBundle\Listener;

use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Icap\BlogBundle\Form\WidgetListType;
use Icap\BlogBundle\Manager\WidgetManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * @DI\Service
 */
class WidgetListener
{
    /** @var WidgetManager */
    private $widgetManager;

    /** @var FormFactoryInterface  */
    private $formFactory;

    /** @var EngineInterface  */
    private $templatingEngine;

    /** @var WidgetListType */
    private $widgetListType;

    /**
     * @DI\InjectParams({
     *      "widgetManager"    = @DI\Inject("icap.blog.manager.widget"),
     *      "formFactory"      = @DI\Inject("form.factory"),
     *      "templatingEngine" = @DI\Inject("templating"),
     *      "widgetListType"   = @DI\Inject("icap_blog.form.widget_list")
     * })
     */
    public function __construct(WidgetManager $widgetManager, FormFactoryInterface $formFactory,
        EngineInterface $templatingEngine, WidgetListType $widgetListType)
    {
        $this->widgetManager    = $widgetManager;
        $this->formFactory      = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->widgetListType   = $widgetListType;
    }

     /**
     * @DI\Observe("widget_blog_list")
     */
    public function onWidgetListDisplay(DisplayWidgetEvent $event)
    {
        $widgetItems = $this->widgetManager->getWidgetList($event->getInstance());

        $content = $this->templatingEngine->render(
            'IcapBlogBundle:widget:list.html.twig',
            array('widgetItems' => $widgetItems)
        );

        $event->setContent($content);

        $event->stopPropagation();
    }

     /**
     * @DI\Observe("widget_blog_list_configuration")
     */
    public function onWidgetListConfigure(ConfigureWidgetEvent $event)
    {
        $form    = $this->formFactory->create($this->widgetListType);
        $content = $this->templatingEngine->render(
            'IcapBlogBundle:widget:listConfigure.html.twig',
            array(
                'form'           => $form->createView(),
                'widgetInstance' => $event->getInstance()
            )
        );

        $event->setContent($content);
    }
}
