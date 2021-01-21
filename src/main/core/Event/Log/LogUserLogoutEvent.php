<?php

namespace Claroline\CoreBundle\Event\Log;

class LogUserLogoutEvent extends LogGenericEvent
{
    const ACTION = 'security.log.user_logout';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(self::ACTION, []);
    }

    public static function getRestriction(): array
    {
        return [self::DISPLAYED_ADMIN];
    }
}
