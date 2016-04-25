<?php

namespace Icap\PortfolioBundle\Listener;

use Claroline\CoreBundle\Event\Notification\NotificationUserParametersEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class NotificationUserParametersListener
{
    /**
     * @param NotificationUserParametersEvent $event
     *
     * @DI\Observe("icap_notification_user_parameters_event")
     */
    public function onGetTypesForParameters(NotificationUserParametersEvent $event)
    {
        $event->addTypes('portfolio');
    }
}
