<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Manager\EventManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service()
 */
class DashboardListener
{
    /** @var TwigEngine */
    private $templating;

    /** @var EventManager */
    private $eventManager;

    /**
     * DashboardListener constructor.
     *
     * @DI\InjectParams({
     *     "eventManager" = @DI\Inject("claroline.event.manager"),
     *     "templating"   = @DI\Inject("templating")
     * })
     *
     * @param EventManager $eventManager
     * @param TwigEngine   $templating
     */
    public function __construct(EventManager $eventManager, TwigEngine $templating)
    {
        $this->eventManager = $eventManager;
        $this->templating = $templating;
    }

    /**
     * Displays dashboard administration tool.
     *
     * @DI\Observe("administration_tool_platform_dashboard")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:administration:dashboard.html.twig', [
                'context' => [
                    'type' => Tool::ADMINISTRATION,
                ],
                'actions' => $this->eventManager->getEventsForApiFilter(LogGenericEvent::DISPLAYED_ADMIN),
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}
