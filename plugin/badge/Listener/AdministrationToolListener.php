<?php

namespace Icap\BadgeBundle\Listener;

use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Listener\Administration\AdministrationToolListener as BaseAdministrationToolListener;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
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
