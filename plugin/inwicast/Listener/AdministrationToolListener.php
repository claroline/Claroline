<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 10/24/16
 */

namespace Icap\InwicastBundle\Listener;

use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Listener\AdministrationToolListener as BaseAdministrationToolListener;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class AdministrationToolListener.
 *
 * @DI\Service()
 */
class AdministrationToolListener extends BaseAdministrationToolListener
{
    /**
     * @DI\Observe("administration_tool_inwicast_configuration")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenInwicastConfiguration(OpenAdministrationToolEvent $event)
    {
        $this->redirect(['_controller' => 'IcapInwicastBundle:MediaCenter:configure'], $event);
    }
}
