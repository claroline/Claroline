<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 4/13/15
 */

namespace Claroline\DropZoneBundle\Listener;

use Claroline\CoreBundle\Event\Notification\NotificationUserParametersEvent;

class NotificationUserParametersListener
{
    public function onGetTypesForParameters(NotificationUserParametersEvent $event)
    {
        $event->addTypes('claroline_dropzone');
    }
}
