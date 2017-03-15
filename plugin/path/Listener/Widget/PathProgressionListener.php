<?php

namespace Innova\PathBundle\Listener\Widget;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\TagBundle\Manager\TagManager;
use Innova\PathBundle\Form\Type\PathWidgetConfigType;
use Innova\PathBundle\Manager\PathManager;
use Innova\PathBundle\Manager\UserProgressionManager;
use Innova\PathBundle\Manager\WidgetManager;
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
     * @var WidgetManager
     */
    private $widgetManager;

    /**
     * @var TagManager
     */
    private $tagManager;

    /**
     * @var PathManager
     */
    private $pathManager;

    /**
     * @var UserProgressionManager
     */
    private $userProgressionManager;

    /**
     * PathWidgetListener.
     *
     * @DI\InjectParams({
     *     "tokenStorage"           = @Di\Inject("security.token_storage"),
     *     "twig"                   = @DI\Inject("templating"),
     *     "formFactory"            = @DI\Inject("form.factory"),
     *     "widgetManager"          = @DI\Inject("innova_path.manager.widget"),
     *     "tagManager"             = @DI\Inject("claroline.manager.tag_manager"),
     *     "pathManager"            = @DI\Inject("innova_path.manager.path"),
     *     "userProgressionManager" = @DI\Inject("innova_path.manager.user_progression")
     * })
     *
     * @param TokenStorageInterface  $tokenStorage
     * @param TwigEngine             $twig
     * @param FormFactoryInterface   $formFactory
     * @param WidgetManager          $widgetManager
     * @param TagManager             $tagManager
     * @param PathManager            $pathManager
     * @param UserProgressionManager $userProgressionManager
     */
    public function __construct(
        TokenStorageInterface  $tokenStorage,
        TwigEngine             $twig,
        FormFactoryInterface   $formFactory,
        WidgetManager          $widgetManager,
        TagManager             $tagManager,
        PathManager            $pathManager,
        UserProgressionManager $userProgressionManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->widgetManager = $widgetManager;
        $this->tagManager = $tagManager;
        $this->pathManager = $pathManager;
        $this->userProgressionManager = $userProgressionManager;
    }

    /**
     * @DI\Observe("widget_innova_path_progression")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        // We can calculate progression only for authenticated users
        $progression = 0;
        if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User) {
            $progression = $this->userProgressionManager->calculateUserProgression(
                $this->tokenStorage->getToken()->getUser(),
                $this->widgetManager->getPaths($event->getInstance())
            );
        }

        $content = $this->twig->render('InnovaPathBundle:Widget:progression.html.twig', [
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
