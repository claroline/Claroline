<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Tool;

use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Manager\EventManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * @DI\Service
 */
class LogsListener
{
    /** @var TwigEngine */
    private $templating;
    /** @var EventManager */
    private $eventManager;

    /**
     * LogsListener constructor.
     *
     * @DI\InjectParams({
     *     "templating"   = @DI\Inject("templating"),
     *     "eventManager" = @DI\Inject("claroline.event.manager")
     * })
     *
     * @param TwigEngine   $templating
     * @param EventManager $eventManager
     */
    public function __construct(
        TwigEngine $templating,
        EventManager $eventManager
    ) {
        $this->templating = $templating;
        $this->eventManager = $eventManager;
    }

    /**
     * Displays logs on Workspace.
     *
     * @DI\Observe("open_tool_workspace_logs")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspace(DisplayToolEvent $event)
    {
        $workspace = $event->getWorkspace();

        $content = $this->templating->render(
            'ClarolineCoreBundle:workspace:logs.html.twig', [
                'workspace' => $workspace,
                'actions' => $this->eventManager->getEventsForApiFilter(LogGenericEvent::DISPLAYED_WORKSPACE),
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }
}
