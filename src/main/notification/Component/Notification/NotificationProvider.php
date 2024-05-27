<?php

namespace Claroline\NotificationBundle\Component\Notification;

use Claroline\AppBundle\Component\AbstractComponentProvider;

/**
 * Aggregates all the notifications defined in the Claroline app.
 *
 * A log MUST :
 *   - be declared as a symfony service and tagged with "claroline.component.notification".
 *   - implement the NotificationInterface interface (or the AbstractNotification class).
 */
class NotificationProvider extends AbstractComponentProvider
{
    public function __construct(
        private readonly iterable $registeredNotifications
    ) {
    }

    final public static function getServiceTag(): string
    {
        return 'claroline.component.notification';
    }

    /**
     * Get the list of all the logs injected in the app by the current plugins.
     * It does not contain logs for disabled plugins.
     */
    protected function getRegisteredComponents(): iterable
    {
        return $this->registeredNotifications;
    }
}
