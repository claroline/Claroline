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

    public function getFunctionalLogs(): array
    {
        return $this->getLogsByType(AbstractFunctionalLog::class);
    }

    public function getMessageLogs(): array
    {
        return $this->getLogsByType(AbstractMessageLog::class);
    }

    public function getOperationalLogs(): array
    {
        return $this->getLogsByType(AbstractOperationalLog::class);
    }

    public function getSecurityLogs(): array
    {
        return $this->getLogsByType(AbstractSecurityLog::class);
    }

    private function getLogsByType(string $typeClassname): array
    {
        $typeLogs = [];
        foreach ($this->registeredLogs as $logHandler) {
            if ($logHandler instanceof $typeClassname) {
                $typeLogs[] = $logHandler::getName();
            }
        }

        return $typeLogs;
    }
}
