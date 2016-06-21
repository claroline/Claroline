<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

/**
 * Interface of the events which are expected to be populated in some way
 * by the listener(s) observing them.
 */
interface DataConveyorEventInterface
{
    /**
     * Checks if the event has been populated.
     *
     * @return bool
     */
    public function isPopulated();
}
