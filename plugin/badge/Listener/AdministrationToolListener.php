<?php

namespace Icap\BadgeBundle\Listener;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Listener\AdministrationToolListener as BaseAdministrationToolListener;

/**
 *  @DI\Service()
 */
class AdministrationToolListener extends BaseAdministrationToolListener
{
    /**
     * @DI\Observe("administration_tool_badges_management")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenBadgesManagement(OpenAdministrationToolEvent $event)
    {
        $this->redirect(['_controller' => 'IcapBadgeBundle:Administration:list'], $event);
    }
}
