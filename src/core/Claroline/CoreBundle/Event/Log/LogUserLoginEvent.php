<?php

namespace Claroline\CoreBundle\Event\Log;

class LogUserLoginEvent extends LogGenericEvent
{
    const ACTION = 'user-login';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(self::ACTION, array());

        $this->setIsDisplayedInAdmin(true);
    }
}
