<?php

namespace Claroline\AppBundle\Component\Context;

use Claroline\AppBundle\Component\AbstractComponentProvider;

/**
 * Aggregates all the contexts defined in the Claroline app.
 */
class ContextProvider extends AbstractComponentProvider
{
    /**
     * The list of all the contexts injected in the app by the current plugins.
     * It does not contain contexts for disabled plugins.
     */
    private iterable $registeredContexts = [];

    public function __construct(iterable $registeredContexts)
    {
        $this->registeredContexts = $registeredContexts;
    }

    protected function getRegisteredComponents(): iterable
    {
        return $this->registeredContexts;
    }
}
