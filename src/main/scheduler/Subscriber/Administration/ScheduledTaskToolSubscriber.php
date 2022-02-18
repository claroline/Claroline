<?php

namespace Claroline\SchedulerBundle\Subscriber\Administration;

use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Scheduled tasks tool.
 */
class ScheduledTaskToolSubscriber implements EventSubscriberInterface
{
    /** @var PlatformConfigurationHandler */
    private $config;

    public function __construct(PlatformConfigurationHandler $config)
    {
        $this->config = $config;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'administration_tool_scheduled_tasks' => 'onDisplayTool',
        ];
    }

    /**
     * Displays scheduled tasks administration tool.
     */
    public function onDisplayTool(OpenToolEvent $event)
    {
        $event->setData([
            'isCronConfigured' => $this->config->getParameter('is_cron_configured') ?? false,
        ]);
        $event->stopPropagation();
    }
}
