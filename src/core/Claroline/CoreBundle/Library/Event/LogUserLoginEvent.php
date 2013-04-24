<?php

namespace Claroline\CoreBundle\Library\Event;

class LogUserLoginEvent extends LogGenericEvent
{
    const ACTION = 'user_login';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(self::ACTION, array());
    }
}