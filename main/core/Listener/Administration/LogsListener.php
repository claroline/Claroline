<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Manager\EventManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service()
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
     * Displays logs administration tool.
     *
     * @DI\Observe("administration_tool_platform_logs")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:administration:logs.html.twig', [
                'actions' => $this->eventManager->getEventsForApiFilter(LogGenericEvent::DISPLAYED_ADMIN),
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}
