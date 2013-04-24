<?php

namespace  Claroline\CoreBundle\Listener;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\EntityManager;
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
    private $em;
    private $securityContext;
    private $twig;
    private $utils;
    private $ed;

    /**
     * @DI\InjectParams({
     *     "em"         = @DI\Inject("doctrine.orm.entity_manager"),
     *     "context"    = @DI\Inject("security.context"),
     *     "twig"       = @DI\Inject("templating"),
     *     "utils"      = @DI\Inject("claroline.security.utilities"),
     *     "ed"         = @DI\Inject("event_dispatcher")
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
        Utilities $utils,
        $ed
    )
    {
        $this->em = $em;
        $this->securityContext = $context;
        $this->twig = $twig;
        $this->utils = $utils;
        $this->ed = $ed;
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

        $logs = $this->em->getRepository('ClarolineCoreBundle:Logger\Log')
            ->findLastLogs($token->getUser(), $roles, $workspace);
        $views = array();

        foreach ($logs as $log) {
            if ($log->getAction() === LogResourceChildUpdateEvent::ACTION) {
                $eventName = 'create_log_list_item_'.$log->getResourceType()->getName();
                $event = new LogCreateDelegateViewEvent($log);
                $this->ed->dispatch($eventName, $event);

                if ($event->getResponseContent() === "") {
                    throw new \Exception(
                        "Event '{$eventName}' didn't receive any response."
                    );
                }

                $views[$log->getId().''] = $event->getResponseContent();
            }
        }

        return $this->twig->render(
            'ClarolineCoreBundle:Log:view_list.html.twig',
            array(
                'logs' => $logs,
                'listItemViews' => $views
            )
        );
    }
}