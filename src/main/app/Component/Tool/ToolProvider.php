<?php

namespace Claroline\AppBundle\Component\Tool;

use Claroline\AppBundle\Component\AbstractComponentProvider;
use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Aggregates all the tools defined in the Claroline app.
 *
 * A tool MUST :
 *   - be declared as a symfony service and tagged with "claroline.component.tool".
 *   - implement the ToolInterface interface (or the AbstractTool class).
 */
class ToolProvider extends AbstractComponentProvider
{
    public function __construct(
        private readonly iterable $registeredTools,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly SecurityManager $securityManager
    ) {
    }

    final public static function getServiceTag(): string
    {
        return 'claroline.component.tool';
    }

    /**
     * Get the list of all the tools injected in the app by the current plugins.
     * It does not contain tools for disabled plugins.
     */
    protected function getRegisteredComponents(): iterable
    {
        return $this->registeredTools;
    }

    public function open(string $toolName, string $context, string $contextId = null): array
    {
        /** @var ToolInterface $tool */
        $tool = $this->getComponent($toolName);

        if (!$tool->supportsContext($context)) {
            throw new \Exception(sprintf('Tool "%s" does not support the context "%s". Check %s::supportsContext() for more info.', $toolName, $context, get_class($tool)));
        }

        // dispatch open event
        $openEvent = new OpenToolEvent($toolName, $context, null, $this->securityManager->getCurrentUser());
        $this->eventDispatcher->dispatch($openEvent, ToolEvents::OPEN);

        return $openEvent->getResponse() ?? [];
    }

    public function configure()
    {
    }

    public function import()
    {
    }

    public function export()
    {
    }
}
