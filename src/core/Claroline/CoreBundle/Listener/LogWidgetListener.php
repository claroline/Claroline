<?php

namespace  Claroline\CoreBundle\Listener;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Library\Event\LogCreateDelegateViewEvent;
use Claroline\CoreBundle\Library\Event\LogResourceChildUpdateEvent;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Security\Utilities;

/**
 * @DI\Service
 */
class LogWidgetListener
{
    private $logManager;
    private $securityContext;
    private $twig;
    private $utils;
    private $ed;

    /**
     * @DI\InjectParams({
     *     "logManager"         = @DI\Inject("claroline.log.manager"),
     *     "context"    = @DI\Inject("security.context"),
     *     "twig"       = @DI\Inject("templating"),
     *     "ed"         = @DI\Inject("event_dispatcher")
     * })
     *
     * @param EntityManager             $em
     * @param SecurityContextInterface  $context
     * @param TwigEngine                $twig
     */
    public function __construct($logManager, SecurityContextInterface $context, TwigEngine $twig, $ed)
    {
        $this->logManager = $logManager;
        $this->securityContext = $context;
        $this->twig = $twig;
        $this->ed = $ed;
    }

    /**
     * @DI\Observe("widget_core_resource_logger_workspace")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onWorkspaceDisplay(DisplayWidgetEvent $event)
    {
        $view = $this->twig->render(
            'ClarolineCoreBundle:Log:view_short_list.html.twig',
            $this->logManager->getWorkspaceWidgetList($event->getWorkspace(), 5)
        );
        $event->setContent($view);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_core_resource_logger_desktop")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDesktopDisplay(DisplayWidgetEvent $event)
    {
        $view = $this->twig->render(
            'ClarolineCoreBundle:Log:view_short_list.html.twig',
            $this->logManager->getWorkspaceWidgetList(null, 5)
        );
        $event->setContent($view);
        $event->stopPropagation();
    }
}