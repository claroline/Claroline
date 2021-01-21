<?php

namespace Claroline\CoreBundle\Event\Log;

class UserLogoutEvent extends LogGenericEvent
{
    const ACTION = 'event.user_logout';

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
