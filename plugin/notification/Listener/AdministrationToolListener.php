<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 4/14/15
 */

namespace Icap\NotificationBundle\Listener;

use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Listener\Administration\AdministrationToolListener as BaseAdministrationToolListener;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class AdminToolListener.
 *
 * @DI\Service()
 */
class AdministrationToolListener extends BaseAdministrationToolListener
{
    /**
     * @DI\Observe("administration_tool_notification_configuration")
     */
    public function onNotificationConfiguration(OpenAdministrationToolEvent $event)
    {
        $this->redirect(['_controller' => 'IcapNotificationBundle:NotificationPluginConfiguration:get'], $event);
    }
}
