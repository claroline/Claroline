<?php

namespace  Claroline\CoreBundle\Listener;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Security\Utilities;

/**
 * @DI\Service
 */
class ResourceLogWidgetListener
{
    private $em;
    private $securityContext;
    private $twig;
    private $utils;

    /**
     * @DI\InjectParams({
     *     "em"         = @DI\Inject("doctrine.orm.entity_manager"),
     *     "context"    = @DI\Inject("security.context"),
     *     "twig"       = @DI\Inject("templating"),
     *     "utils"      = @DI\Inject("claroline.security.utilities")
     * })
     *
     * @param EntityManager             $em
     * @param SecurityContextInterface  $context
     * @param TwigEngine                $twig
     */
    public function __construct(
        EntityManager $em,
        SecurityContextInterface $context,
        TwigEngine $twig,
        Utilities $utils
    )
    {
        $this->em = $em;
        $this->securityContext = $context;
        $this->twig = $twig;
        $this->utils = $utils;
    }

    /**
     * @DI\Observe("widget_core_resource_logger_workspace")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onWorkspaceDisplay(DisplayWidgetEvent $event)
    {
        $event->setContent($this->renderLogs($event->getWorkspace()));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_core_resource_logger_desktop")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDesktopDisplay(DisplayWidgetEvent $event)
    {
        $event->setContent($this->renderLogs());
        $event->stopPropagation();
    }

    private function renderLogs(AbstractWorkspace $workspace = null)
    {
        $token = $this->securityContext->getToken();
        $roles = $this->utils->getRoles($token);

        $logs = $this->em->getRepository('ClarolineCoreBundle:Logger\ResourceLog')
            ->findLastLogs($roles, $workspace);

        return $this->twig->render(
            'ClarolineCoreBundle:Widget:resource_events.html.twig',
            array('logs' => $logs)
        );
    }
}