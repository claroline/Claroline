<?php

namespace Innova\PathBundle\Listener\Widget;

use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\TagBundle\Manager\TagManager;
use Innova\PathBundle\Form\Type\PathWidgetConfigType;
use Innova\PathBundle\Manager\WidgetManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Manages Path slide show widgets.
 *
 * @DI\Service()
 */
class PathSlideshowListener
{
    /**
     * @var TwigEngine
     */
    private $twig;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var WidgetManager
     */
    private $widgetManager;

    /**
     * @var TagManager
     */
    private $tagManager;

    /**
     * PathWidgetListener.
     *
     * @DI\InjectParams({
     *     "twig"          = @DI\Inject("templating"),
     *     "formFactory"   = @DI\Inject("form.factory"),
     *     "widgetManager" = @DI\Inject("innova_path.manager.widget"),
     *     "tagManager"    = @DI\Inject("claroline.manager.tag_manager")
     * })
     *
     * @param TwigEngine           $twig
     * @param FormFactoryInterface $formFactory
     * @param WidgetManager        $widgetManager
     * @param TagManager           $tagManager
     */
    public function __construct(
        TwigEngine           $twig,
        FormFactoryInterface $formFactory,
        WidgetManager        $widgetManager,
        TagManager           $tagManager)
    {
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->widgetManager = $widgetManager;
        $this->tagManager = $tagManager;
    }

    /**
     * @DI\Observe("widget_innova_path_slideshow")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $content = $this->twig->render('InnovaPathBundle:Widget:slideshow.html.twig', [
            'paths' => $this->widgetManager->getPaths($event->getInstance(), true),
        ]);

        $event->setContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_innova_path_slideshow_configuration")
     *
     * @param ConfigureWidgetEvent $event
     */
    public function onConfigure(ConfigureWidgetEvent $event)
    {
        $instance = $event->getInstance();
        $config = $this->widgetManager->getConfig($instance);

        $form = $this->formFactory->create(new PathWidgetConfigType(), $config);
        $content = $this->twig->render(
            'InnovaPathBundle:Widget:config.html.twig',
            [
                'form' => $form->createView(),
                'instance' => $instance,
                'tags' => $this->tagManager->getPlatformTags(),
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }
}
