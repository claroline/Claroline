<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Manager\EventManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class DashboardListener
{
    /** @var EventManager */
    private $eventManager;

    /**
     * DashboardListener constructor.
     *
     * @DI\InjectParams({
     *     "eventManager" = @DI\Inject("claroline.event.manager")
     * })
     *
     * @param EventManager $eventManager
     */
    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Displays dashboard administration tool.
     *
     * @DI\Observe("administration_tool_dashboard")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $event->setData([
            'actions' => $this->eventManager->getEventsForApiFilter(LogGenericEvent::DISPLAYED_ADMIN),
        ]);
        $event->stopPropagation();
    }
}
