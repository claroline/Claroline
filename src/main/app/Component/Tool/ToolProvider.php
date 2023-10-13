<?php

namespace Claroline\AppBundle\Component\Tool;

use Claroline\AppBundle\Component\AbstractComponentProvider;

/**
 * Aggregates all the tools defined in the Claroline app.
 */
class ToolProvider extends AbstractComponentProvider
{
    /**
     * The list of all the tools injected in the app by the current plugins.
     * It does not contain tools for disabled plugins.
     */
    private iterable $registeredTools = [];

    public function __construct(iterable $toolComponents)
    {
        $this->registeredTools = $toolComponents;
    }

    protected function getRegisteredComponents(): iterable
    {
        return $this->registeredTools;
    }
}
