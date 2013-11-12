<?php

namespace Claroline\CoreBundle\Listener\Tool;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AgendaListener {


    /**
     * @DI\Observe("configure_workspace_tool_agenda")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplay()
    {

    }
}