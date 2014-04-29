<?php

namespace Claroline\ScormBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;

class LogScormResultEvent extends LogGenericEvent {

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }
}
