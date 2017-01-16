<?php

namespace Innova\PathBundle\EventListener\Widget;

use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\TagBundle\Manager\TagManager;
use Innova\PathBundle\Manager\PathManager;
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
     * @var \Symfony\Bundle\TwigBundle\TwigEngine
     */
    private $twig;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Innova\PathBundle\Manager\PathManager
     */
    private $pathManager;

    /**
     * PathWidgetListener.
     *
     * @DI\InjectParams({
     *     "twig"        = @DI\Inject("templating"),
     *     "formFactory" = @DI\Inject("form.factory"),
     *     "pathManager" = @DI\Inject("innova_path.manager.path")
     * })
     *
     * @param TwigEngine           $twig
     * @param FormFactoryInterface $formFactory
     * @param PathManager          $pathManager
     */
    public function __construct(
        TwigEngine           $twig,
        FormFactoryInterface $formFactory,
        PathManager          $pathManager)
    {
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->pathManager = $pathManager;

    }

    /**
     * @DI\Observe("widget_innova_path_slideshow")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $widgetInstance = $event->getInstance();
        $workspace = $widgetInstance->getWorkspace();

        $config = $this->pathManager->getWidgetConfig($widgetInstance);

        $content = $this->twig->render('InnovaPathBundle:Widget:slideshow.html.twig', [
            'workspace' => $workspace,
            'isDesktop' => $widgetInstance->isDesktop(),
            'paths' => $this->pathManager->getWidgetPaths($config, $workspace),
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
        $config = $this->pathManager->getWidgetConfig($instance);

        $form = $this->formFactory->create('innova_path_widget_config', $config);
        $content = $this->twig->render(
            'InnovaPathBundle:Widget:config.html.twig',
            [
                'form' => $form->createView(),
                'instance' => $instance,
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }
}
