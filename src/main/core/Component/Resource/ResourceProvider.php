<?php

namespace Claroline\CoreBundle\Component\Resource;

use Claroline\AppBundle\Component\AbstractComponentProvider;

/**
 * Aggregates all the resources defined in the Claroline app.
 *
 * A tool MUST :
 *   - be declared as a symfony service and tagged with "claroline.component.resource".
 *   - implement the ResourceInterface interface (or the ResourceComponent class).
 */
class ResourceProvider extends AbstractComponentProvider
{
    public function __construct(
        private readonly iterable $registeredResources,
    ) {
    }

    final public static function getServiceTag(): string
    {
        return 'claroline.component.resource';
    }

    /**
     * Get the list of all the tools injected in the app by the current plugins.
     * It does not contain tools for disabled plugins.
     */
    protected function getRegisteredComponents(): iterable
    {
        return $this->registeredResources;
    }
}