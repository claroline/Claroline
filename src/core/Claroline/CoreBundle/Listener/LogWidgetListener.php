<?php

namespace  Claroline\CoreBundle\Listener;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

/**
 * @DI\Service
 */
class LogWidgetListener
{
    private $em;
    private $securityContext;
    private $twig;

    /**
     * @DI\InjectParams({
     *     "em"         = @DI\Inject("doctrine.orm.entity_manager"),
     *     "context"    = @DI\Inject("security.context"),
     *     "twig"       = @DI\Inject("templating")
     * })
     *
     * @param EntityManager             $em
     * @param SecurityContextInterface  $context
     * @param TwigEngine                $twig
     */
    public function __construct(EntityManager $em, SecurityContextInterface $context, TwigEngine $twig)
    {
        $this->em = $em;
        $this->securityContext = $context;
        $this->twig = $twig;
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
         $logs = $this->em->getRepository('ClarolineCoreBundle:Logger\Log')
            ->findLastLogs($this->securityContext->getToken()->getUser(), $workspace);

        return $this->twig->render(
            'ClarolineCoreBundle:Log:view_list.html.twig',
            array('logs' => $logs)
        );
    }
}