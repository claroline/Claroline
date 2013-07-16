<?php

namespace Claroline\CoreBundle\Event;

/**
 * Interface of the events which are expected to be populated in some way
 * by the listener(s) observing them.
 */
interface DataConveyorEventInterface
{
    /**
     * Checks if the event has been populated?
     *
     * @return boolean
     */
    public function isPopulated();
}