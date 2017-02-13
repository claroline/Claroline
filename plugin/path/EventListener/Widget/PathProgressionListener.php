<?php

namespace Innova\PathBundle\EventListener\Widget;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Innova\PathBundle\Manager\PathManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Manages User progression widgets.
 *
 * @DI\Service()
 */
class PathProgressionListener
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var TwigEngine
     */
    private $twig;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var PathManager
     */
    private $pathManager;

    /**
     * PathWidgetListener.
     *
     * @DI\InjectParams({
     *     "tokenStorage" = @Di\Inject("security.token_storage"),
     *     "twig"         = @DI\Inject("templating"),
     *     "formFactory"  = @DI\Inject("form.factory"),
     *     "pathManager"  = @DI\Inject("innova_path.manager.path")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     * @param TwigEngine            $twig
     * @param FormFactoryInterface  $formFactory
     * @param PathManager           $pathManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        TwigEngine           $twig,
        FormFactoryInterface $formFactory,
        PathManager          $pathManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->pathManager = $pathManager;
    }

    /**
     * @DI\Observe("widget_innova_path_progression")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $widgetInstance = $event->getInstance();
        $workspace = $widgetInstance->getWorkspace();

        // We can calculate progression only for authenticated users
        $progression = 0;
        if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User) {
            $config = $this->pathManager->getWidgetConfig($widgetInstance);

            $progression = $this->pathManager->calculateUserProgression(
                $this->tokenStorage->getToken()->getUser(),
                $this->pathManager->getWidgetPaths($config, $workspace)
            );
        }

        $content = $this->twig->render('InnovaPathBundle:Widget:progression.html.twig', [
            'workspace' => $workspace,
            'isDesktop' => $widgetInstance->isDesktop(),
            'userProgression' => $progression,
        ]);

        $event->setContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_innova_path_progression_configuration")
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
