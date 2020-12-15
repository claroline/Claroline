<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 5/18/15
 */

namespace Icap\SocialmediaBundle\Listener;

use Claroline\CoreBundle\Event\Notification\NotificationUserParametersEvent;

/**
 * Class NotificationUserParametersListener.
 */
class NotificationUserParametersListener
{
    /**
     * @param NotificationUserParametersEvent $event
     */
    public function onGetTypesForParameters(NotificationUserParametersEvent $event)
    {
        $event->addTypes('icap_socialmedia');
    }
}
