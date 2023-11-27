<?php

namespace Claroline\LogBundle\Component\Log;

use Claroline\AppBundle\Component\AbstractComponentProvider;

/**
 * Aggregates all the logs defined in the Claroline app.
 *
 * A log MUST :
 *   - be declared as a symfony service and tagged with "claroline.component.context".
 *   - implement one of the Abstract*Log class depending on the log to create.
 */
class LogProvider extends AbstractComponentProvider
{
    public function __construct(
        private readonly iterable $registeredLogs
    ) {
    }

    final public static function getServiceTag(): string
    {
        return 'claroline.component.log';
    }

    /**
     * Get the list of all the logs injected in the app by the current plugins.
     * It does not contain logs for disabled plugins.
     */
    protected function getRegisteredComponents(): iterable
    {
        return $this->registeredLogs;
    }
}
