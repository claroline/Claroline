<?php

namespace Claroline\AppBundle\Component\Context;

use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\AbstractToolEvent;
use Claroline\CoreBundle\Event\Tool\CloseToolEvent;
use Claroline\CoreBundle\Event\Tool\ConfigureToolEvent;
use Claroline\CoreBundle\Event\Tool\ExportToolEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractToolSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::OPEN => 'open',
            ToolEvents::CLOSE => 'close',
            ToolEvents::CONFIGURE => 'configure',
            ToolEvents::EXPORT => 'export',
            ToolEvents::IMPORT => 'import',
        ];
    }

    /**
     * Checks if the subscriber supports the tool.
     */
    abstract public static function supportsTool(string $toolName): bool;

    /**
     * Do something when the tool is opened.
     */
    abstract protected function onOpen(OpenToolEvent $event): void;

    /**
     * Do something when the tool is closed.
     */
    abstract protected function onClose(CloseToolEvent $event): void;

    /**
     * Do something when the tool is configured.
     */
    abstract protected function onConfigure(ConfigureToolEvent $event): void;

    /**
     * Do something when the tool is exported.
     */
    abstract protected function onExport(ExportToolEvent $event): void;

    /**
     * Do something when the tool is imported.
     */
    abstract protected function onImport(ExportToolEvent $event): void;

    final public function __call($method, $arguments): void
    {
        $this->forwardEvent($arguments[0], 'on'.ucfirst($method));
    }

    private function forwardEvent(AbstractToolEvent $event, string $handler): void
    {
        // checks if the subscriber instance supports this tool
        if (!static::supportsTool($event->getToolName())) {
            // tool is not supported, stop event subscriber execution
            return;
        }

        // forward event to the subscriber instance
        call_user_func([$this, $handler], [$event]);
    }
}
