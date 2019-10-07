<?php

namespace UJM\ExoBundle\Listener\Tool;

use Claroline\CoreBundle\Event\DisplayToolEvent;

class QuestionBankListener
{
    /**
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $event->stopPropagation();
    }
}
