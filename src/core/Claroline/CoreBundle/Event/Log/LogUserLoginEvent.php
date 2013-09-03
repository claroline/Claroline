<?php

namespace Claroline\CoreBundle\Event\Log;

class LogUserLoginEvent extends LogGenericEvent
{
    const ACTION = 'user_login';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(self::ACTION, array());

        $this->isDisplayedInAdmin(true);
    }
}
